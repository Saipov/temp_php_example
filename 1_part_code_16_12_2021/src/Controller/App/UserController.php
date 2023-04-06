<?php

namespace App\Controller\App;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use App\Classes\AbstractController;
use App\Component\CommandBus\CommandBus;
use App\Component\CommandBus\Input\Input;
use App\DBAL\LogActionType;
use App\Entity as Entity;
use App\Exception\Http as Http;
use App\Repository\ContactRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Security\Voter\UserManagementVoter;
use App\Validator\PropertyValidator\PropertyValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController
 *
 * @package App\Controller\App
 * @Route("/users")
 */
class UserController extends AbstractController
{
    /**
     * @Route(methods={"GET"})
     * @param Request                                                      $request
     * @param CommandBus                                                   $commandBus
     * @param \Symfony\Component\Serializer\Normalizer\NormalizerInterface $normalizer
     * @param \App\Repository\UserRepository                               $userRepository
     *
     * @return JsonResponse
     * @throws \App\Exception\Http\ForbiddenException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getUsers(
        Request             $request,
        CommandBus          $commandBus,
        NormalizerInterface $normalizer,
        UserRepository      $userRepository
    ): JsonResponse
    {
        $input = new Input($request->query->all());

        // Для руководителей групп, пользователи своей группы.
        if ($this->isGranted("ROLE_TEAM_LEADER")) {
            if (!$this->getUser()->getGroup() instanceof Entity\UserGroup) {
                throw new Http\ForbiddenException("To access the list, you need to join the group.");
            }
            // Для руководителя группы переопределить данный параметр.
            $input->setParameter("user_group_id", $this->getUser()->getGroup()->getId());
        }

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator $paginator */
        $paginator = $commandBus->get("UserGet")
            ->setup($input)
            ->validate()
            ->handle()
            ->getData();

        return $this->json([
            "meta" => [
                "type" => "array",
                "count" => $paginator->count(),
                "users_online" => $userRepository->getOnlineCount()
            ],
            "data" => $normalizer->normalize($paginator->getQuery()->getResult())
        ]);
    }


    /**
     * Создать нового пользователя
     *
     * @Route(methods={"POST"})
     * @param Request    $request
     * @param CommandBus $commandBus
     *
     * @return Response
     */
    public function userCreate(
        Request    $request,
        CommandBus $commandBus
    ): Response
    {
        $input = new Input($request->request->all());

        // Руководитель группы создаёт пользователя и включает его в свою группу.
        if ($this->isGranted("ROLE_TEAM_LEADER")) {
            $input->setParameter("user_group_id", $this->getUser()->getId());
        }

        $data = $commandBus
            ->get("UserCreate")
            ->setup($input)
            ->validate()
            ->handle()
            ->getData();

        return $this->json($data, 201);
    }

    /**
     * @Route(
     *     path="/create-validations",
     *     methods={"POST"}
     *     )
     * @param Request    $request
     * @param CommandBus $commandBus
     *
     * @return Response
     */
    public function createValidations(
        Request    $request,
        CommandBus $commandBus
    ): Response
    {
        $input = new Input($request->request->all());
        $data = $commandBus
            ->get("UserCreateValidation")
            ->setup($input)
            ->handle()
            ->getData();

        return $this->json($data);
    }


    /**
     * @Route(
     *     path="/{id}",
     *     requirements={"id": "\d+"},
     *     methods={"PATCH"}
     *     )
     * @param int        $id
     * @param Request    $request
     * @param CommandBus $commandBus
     *
     * @return Response
     */
    public function edit(
        int        $id,
        Request    $request,
        CommandBus $commandBus
    ): Response
    {
        $input = new Input($request->request->all());
        $input->setParameter("user_id", $id);

        $commandBus
            ->get("UserEdit")
            ->setup($input)
            ->handle();

        return new Response();
    }


    /**
     * Удаление пользователя системы.
     *
     * @Route(
     *     path="/{user_id}",
     *     requirements={"user_id": "\d+"},
     *     methods={"DELETE"}
     *     )
     * @param int                    $user_id
     * @param Request                $request
     * @param UserRepository         $userRepository
     * @param ContactRepository      $contactRepository
     * @param EntityManagerInterface $entityManager
     * @param PropertyValidator      $validator
     *
     * @return Response
     * @throws Http\BadRequestException
     * @throws Http\NotFoundException
     * @throws Exception
     */
    public function delete(
        int $user_id,
        Request $request,
        UserRepository $userRepository,
        ContactRepository $contactRepository,
        EntityManagerInterface $entityManager,
        PropertyValidator $validator
    ): Response
    {
        $validator->validate($request->request->all(), [
            "receiver_user_id" => [
                new Assert\Optional([
                    "constraints" => [
                        new Assert\Positive()
                    ]
                ])
            ]
        ]);

        $targetUser = $userRepository->find($user_id);

        if (!$targetUser instanceof Entity\User) {
            throw new Http\NotFoundException("User not found.", "user_not_found");
        }

        $this->denyAccessUnlessGranted(UserManagementVoter::DELETE, $targetUser);

        try {
            $entityManager->getConnection()->beginTransaction();

            // Если есть кому передать контакты, передаём
            if ($request->request->has("receiver_user_id")) {
                $contactRepository->moveContacts($user_id, $request->get("receiver_user_id"));
            } else {
                // Удаление контактов
                $targetUser->getContacts()
                    ->forAll(function (Entity\Contact $contact) {
                        $contact->delete();
                        return true;
                    });
            }

            // Устанавливаю дату удаления, она же будет означать, что пользователь удалён.
            $targetUser->setDeletedAt(new DateTime());

            $entityManager->flush();
            $entityManager->commit();
        } catch (Exception $exception) {
            $entityManager->rollback();
            throw $exception;
        }

        $this->logInfo(LogActionType::USER_DELETE, [
            "id" => $user_id
        ]);

        // Теперь это пользователь для Doctrine не существует!
        return new Response("", Response::HTTP_NO_CONTENT);
    }


    /**
     * @Route(
     *     path="/{id}",
     *     requirements={"id": "\d+"},
     *     methods={"GET"}
     *     )
     * @param int                 $id
     * @param Request             $request
     * @param UserRepository      $userRepository
     * @param NormalizerInterface $normalizer
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws Http\ForbiddenException
     * @throws Http\NotFoundException
     */
    public function getById(
        int $id,
        Request $request,
        UserRepository $userRepository,
        NormalizerInterface $normalizer
    ): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user instanceof Entity\User) {
            throw new Http\NotFoundException("User not found.");
        }

        if ($this->isGranted("ROLE_TEAM_LEADER")) {
            // Руководитель группы может редактировать только свои пользователей.

            if (!$this->getUser()->getGroup() instanceof Entity\UserGroup) {
                throw new Http\ForbiddenException("Access denied!");
            }

            if (!$user->getGroup() instanceof Entity\UserGroup) {
                throw new Http\ForbiddenException("Access denied!");
            }

            if ($user->getGroup()->getId() != $this->getUser()->getGroup()->getId()) {
                throw new Http\ForbiddenException("Access denied!");
            }
        }

        $userNormalized = $normalizer->normalize($user, "json", [
            "project",
            "organization",
            "role",
            "role.permissions",
            "country",
            "pbx_configuration.credentials",
            "pbx_configuration.rtc_configuration"
        ]);

        return $this->json($userNormalized);
    }


    /**
     * Установить проект пользователю
     *
     * @Route(
     *     path="/{user_id}/projects/{project_id}",
     *     requirements={"user_id": "\d+"},
     *     requirements={"project_id": "\d+"},
     *     methods={"GET", "PATCH"}
     * )
     * @param int                    $user_id
     * @param int                    $project_id
     * @param Request                $request
     * @param UserRepository         $userRepository
     * @param ProjectRepository      $projectRepository
     * @param EntityManagerInterface $manager
     * @param TranslatorInterface    $translator
     *
     * @return Response
     * @throws Http\NotFoundException
     */
    public function setProject(
        int $user_id,
        int $project_id,
        Request $request,
        UserRepository $userRepository,
        ProjectRepository $projectRepository,
        EntityManagerInterface $manager,
        TranslatorInterface $translator
    ): Response
    {
        $project = $projectRepository->find($project_id);

        if (!$project instanceof Entity\Project) {
            throw new Http\NotFoundException("The project was not found.");
        }

        $user = $userRepository->find($user_id);

        if (!$user instanceof Entity\User) {
            throw new Http\NotFoundException($translator->trans("User not found.", [], null, $request->getLocale()), "user_not_found");
        }

        $user->setProject($project);

        $manager->flush();

        $this->logInfo(LogActionType::USER_SETPROJECT, [
            "id" => $user_id,
            "project_id" => $project_id
        ]);

        return new Response("", Response::HTTP_NO_CONTENT);
    }

    /**
     * Установить проект пользователю
     *
     * @Route(
     *     path="/{user_id}/pbx-configuration",
     *     requirements={"user_id": "\d+"},
     *     methods={"GET", "PUT", "DELETE"}
     * )
     * @param int                                                          $user_id
     * @param Request                                                      $request
     * @param CommandBus                                                   $commandBus
     * @param \Symfony\Component\Serializer\Normalizer\NormalizerInterface $normalizer
     *
     * @return Response
     * @throws \App\Exception\Http\NotFoundException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function pbxConfig(
        int                 $user_id,
        Request             $request,
        CommandBus          $commandBus,
        NormalizerInterface $normalizer
    ): Response
    {
        $input = new Input($request->request->all());
        $input->setParameter("user_id", $user_id);

        if ($request->getMethod() === "PUT") {
            $commandBus
                ->get("UserPBXConfigSave")
                ->setup($input)
                ->handle();

        } else if ($request->getMethod() === "GET") {
            $data = $commandBus
                ->get("UserPBXConfigGet")
                ->setup($input)
                ->handle()
                ->getData();

            return $this->json($normalizer->normalize($data));
        } else if ($request->getMethod() === "DELETE") {
            $commandBus
                ->get("UserPBXConfigDelete")
                ->setup(new Input(["user_id" => $user_id]))
                ->handle();

            return new Response();
        }
        throw new Http\NotFoundException("HTTP Not Found");
    }

    /**
     * @Route(
     *     path="/login_verification",
     *     methods={"GET"}
     * )
     * @param Request             $request
     * @param CommandBus          $commandBus
     * @param NormalizerInterface $normalizer
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function loginVerification(
        Request $request,
        CommandBus $commandBus,
        NormalizerInterface $normalizer
    ): JsonResponse
    {
        $data = $commandBus
            ->get("UserLoginVerification")
            ->setup(new Input([
                "q" => $request->get("q", ""),
            ]))
            ->handle()
            ->getData();

        return $this->json([
            "data" => $normalizer->normalize($data)
        ]);
    }

    /**
     * Возвращает список последних активных сессий.
     *
     * @Route(
     *     path="/{user_id}/sessions",
     *     methods={"GET"}
     * )
     * @param int                 $user_id
     * @param Request             $request
     * @param CommandBus          $commandBus
     * @param NormalizerInterface $normalizer
     *
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getSessions(
        int                 $user_id,
        Request             $request,
        CommandBus          $commandBus,
        NormalizerInterface $normalizer
    ): JsonResponse
    {
        $input = new Input($request->query->all());
        $input->setParameter("user_id", $user_id);

        /** @var \Doctrine\ORM\Tools\Pagination\Paginator $paginator */
        $paginator = $commandBus
            ->get("UserGetSessions")
            ->setup($input)
            ->validate()
            ->handle()
            ->getData();

        return $this->json([
            "meta" => [
                "count" => $paginator->count()
            ],
            "data" => $normalizer->normalize($paginator->getQuery()->getResult())
        ]);
    }
}
