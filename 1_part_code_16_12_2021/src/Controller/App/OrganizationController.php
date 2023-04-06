<?php

namespace App\Controller\App;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use App\Classes\AbstractController;
use App\DBAL\LogActionType;
use App\DBAL\RoleType;
use App\Entity\Organization;
use App\Entity\OrganizationTag;
use App\Entity\User;
use App\Entity\UserRole;
use App\Exception\Http\BadRequestException;
use App\Exception\Http\ConflictException;
use App\Exception\Http\ForbiddenException;
use App\Exception\Http\NotFoundException;
use App\Repository\OrganizationRepository;
use App\Repository\OrganizationTagRepository;
use App\Repository\UserRepository;
use App\Service\JWT;
use App\Validator\PropertyValidator\Constraints\OrganizationScheme;
use App\Validator\PropertyValidator\PropertyValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class OrganizationController
 *
 * @package App\Controller\App
 * @Route("/organizations")
 */
class OrganizationController extends AbstractController
{
    /**
     * @Route(methods={"GET"})
     * @param Request                $request
     * @param OrganizationRepository $organizationRepository
     * @param NormalizerInterface    $normalizer
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function find(
        Request $request,
        OrganizationRepository $organizationRepository,
        NormalizerInterface $normalizer
    ): JsonResponse
    {

        $organizations = $organizationRepository->findOrganizations(
            $request->get("q", ""),
            [],
            $request->get("offset", 0),
            $request->get("count", 100)
        );
        $lastQueryCount = $organizationRepository->getLastQueryCount();

        // Нормализую данные
        $organizationsNormalized = $normalizer->normalize($organizations, null, ["responsible.full"]); // more option are "responsible.short", "tags", "site"

        $this->logInfo(LogActionType::ORGANIZATION_FIND);

        return $this->json([
            "meta" => [
                "type" => "array",
                "count" => $lastQueryCount
            ],
            "data" => $organizationsNormalized
        ]);
    }


    /**
     * @Route(
     *     path="/{id}",
     *     requirements={"id": "\d+"},
     *     methods={"GET"}
     *     )
     * @param int                    $id
     * @param OrganizationRepository $organizationRepository
     * @param NormalizerInterface    $normalizer
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws NotFoundException
     */
    public function getById(
        int $id,
        OrganizationRepository $organizationRepository,
        NormalizerInterface $normalizer
    ): JsonResponse
    {
        $organization = $organizationRepository->find($id);
        if (!$organization instanceof Organization) {
            throw new NotFoundException("Organization not found", "organization_not_found");
        }

        $this->logInfo(LogActionType::ORGANIZATION_GETBYID, [
            "id" => $id
        ]);

        $organizationNormalized = $normalizer->normalize($organization, null, ["responsible.full", "tags", "site"]); // one more option is "responsible.short"
        return $this->json($organizationNormalized);
    }


    /**
     * @Route(
     *     path="/my",
     *     methods={"GET"}
     *     )
     * @param NormalizerInterface $normalizer
     * @param User                $currentUser
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws NotFoundException
     */
    public function getMyOrganization(
        NormalizerInterface $normalizer,
        User $currentUser
    )
    {
        $organization = $currentUser->getOrganization();
        if (!$organization instanceof Organization) {
            throw new NotFoundException("Organization not found", "organization_not_found");
        }

        $this->logInfo(LogActionType::ORGANIZATION_MY);

        $organizationNormalized = $normalizer->normalize($organization, null, ["responsible.full", "tags", "site"]); // one more option is "responsible.short"
        return $this->json($organizationNormalized);
    }


    /**
     * @Route(
     *     methods={"POST"}
     * )
     * @param Request                   $request
     * @param UserRepository            $userRepository
     * @param OrganizationTagRepository $organizationTagRepository
     * @param EntityManagerInterface    $manager
     * @param PropertyValidator         $propertyValidator
     * @param ValidatorInterface        $validator
     *
     * @return Response
     * @throws BadRequestException
     * @throws ConflictException
     */
    public function create(
        Request $request,
        UserRepository $userRepository,
        OrganizationTagRepository $organizationTagRepository,
        EntityManagerInterface $manager,
        PropertyValidator $propertyValidator,
        ValidatorInterface $validator
    ): Response
    {
        $propertyValidator->validate($request->request->all(), OrganizationScheme::class);

        $organization = new Organization();
        $organization->setName($request->get("name"));
        $organization->setInn($request->get("inn"));
        $organization->setCpp($request->get("cpp"));
        $organization->setSphereActivity($request->get("sphere_activity"));
        $organization->setSite($request->get("site"));

        if ($request->request->has("responsible")) {
            $organization->setResponsible($userRepository->find($request->get("responsible")));
        }

        $organization->setPhone($request->get("phone"));
        $organization->setEmail($request->get("email"));
        $organization->setCity($request->get("city"));
        $organization->setRegion($request->get("region"));
        $organization->setAddress($request->get("address"));
        $organization->setDescription($request->get("description"));

        // Tags
        foreach ($request->get("tags", []) as $item) {
            if (is_numeric($item)) {
                $tag = $organizationTagRepository->find($item);
                $organization->addTag($tag);
            } else {
                $tag = new OrganizationTag();
                $tag->setName($item);
            }

            if ($tag instanceof OrganizationTag) {
                $organization->addTag($tag);
                $tag->setOrganization($organization);
            }
        }

        $violations = $validator->validate($organization);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    "property_name" => $violation->getPropertyPath(),
                    "message" => $violation->getMessage(),
                    "code" => $violation->getCode()
                ];
            }
            throw new ConflictException("Компанию сохранить не удалось", "conflict", $errors);
        }

        $manager->persist($organization);
        $manager->flush();

        $this->logInfo(LogActionType::ORGANIZATION_CREATE, [
            "id" => $organization->getId()
        ]);

        return $this->json([
            "id" => $organization->getId()
        ], Response::HTTP_CREATED
        );
    }


    /**
     * @Route(
     *     path="/{id}",
     *     requirements={"id": "\d+"},
     *     methods={"PUT"}
     * )
     * @param int                       $id
     * @param Request                   $request
     * @param UserRepository            $userRepository
     * @param OrganizationRepository    $organizationRepository
     * @param OrganizationTagRepository $organizationTagRepository
     * @param EntityManagerInterface    $manager
     * @param PropertyValidator         $propertyValidator
     *
     * @return Response
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function put(
        int $id,
        Request $request,
        UserRepository $userRepository,
        OrganizationRepository $organizationRepository,
        OrganizationTagRepository $organizationTagRepository,
        EntityManagerInterface $manager,
        PropertyValidator $propertyValidator
    ): Response
    {
        $propertyValidator->validate($request->request->all(), OrganizationScheme::class);

        $organization = $organizationRepository->find($id);

        if (!$organization instanceof Organization) {
            throw new NotFoundException("Organization not found.", "organization_not_found");
        }

        $organization->setName($request->get("name"));
        $organization->setInn($request->get("inn"));
        $organization->setCpp($request->get("cpp"));
        $organization->setSphereActivity($request->get("sphere_activity", null));
        $organization->setSite($request->get("site"));
        $organization->setResponsible($userRepository->find($request->get("responsible", 0)));
        $organization->setEmail($request->get("email"));
        $organization->setPhone($request->get("phone"));
        $organization->setCity($request->get("city"));
        $organization->setRegion($request->get("region"));
        $organization->setAddress($request->get("address"));
        $organization->setDescription($request->get("description"));

        // Tags
        $organization->getTags()->clear();
        foreach ($request->get("tags", []) as $item) {
            if (is_numeric($item)) {
                $tag = $organizationTagRepository->find($item);
            } else {
                $tag = new OrganizationTag();
                $tag->setName($item);
                $tag->setOrganization($organization);
                $manager->persist($tag);
            }

            if ($tag instanceof OrganizationTag) {
                $organization->addTag($tag);
                $manager->persist($organization);
            }
        }

        $manager->persist($organization);
        $manager->flush();

        $this->logInfo(LogActionType::ORGANIZATION_PUT, [
            "id" => $id
        ]);

        return new Response("", Response::HTTP_NO_CONTENT);
    }


    /**
     * @Route(
     *     path="/{id}",
     *     methods={"DELETE"}
     *     )
     * @param int                    $id
     * @param OrganizationRepository $organizationRepository
     * @param EntityManagerInterface $manager
     *
     * @return Response
     * @throws NotFoundException
     */
    public function delete(
        int $id,
        OrganizationRepository $organizationRepository,
        EntityManagerInterface $manager
    )
    {
        $organization = $organizationRepository->find($id);

        if (!$organization instanceof Organization) {
            throw new NotFoundException("Organization not found.");
        }

        $manager->remove($organization);
        $manager->flush();

        $this->logInfo(LogActionType::ORGANIZATION_DELETE, [
            "id" => $id
        ]);

        return new Response("", Response::HTTP_NO_CONTENT);
    }


    /**
     * @Route(
     *     path="/tags",
     *     methods={"GET"}
     * )
     * @param NormalizerInterface    $normalizer
     * @param OrganizationRepository $organizationRepository
     * @param User                   $currentUser
     * @param UserRole               $currentUserRole
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function getTags(
        NormalizerInterface $normalizer,
        User $currentUser,
        UserRole $currentUserRole
    )
    {
        $tags = new ArrayCollection();
        if (in_array($currentUserRole->getId(), [
            RoleType::R_LEADER_CC,
            RoleType::R_OPERATOR,
            RoleType::R_TEAM_LEADER
        ])) {
            $tags = $currentUser->getOrganization()->getTags();
        }

        $tagsNormalized = $normalizer->normalize($tags, "json", [
            AbstractNormalizer::ATTRIBUTES => [
                "id",
                "name"
            ]
        ]);

        $this->logInfo(LogActionType::ORGANIZATION_GETTAGS);

        return $this->json($tagsNormalized);
    }


    /**
     * @Route(
     *     path="/access-token/generate",
     *     methods={"GET"}
     * )
     * @param Request             $request
     * @param TranslatorInterface $translator
     * @param User                $currentUser
     * @param UserRole            $currentUserRole
     * @param JWT                 $JWT
     *
     * @return Response
     * @throws ForbiddenException
     */
    public function generatePersonalAccessToken(
        Request $request,
        TranslatorInterface $translator,
        User $currentUser,
        UserRole $currentUserRole,
        JWT $JWT
    ): Response
    {
//				if (!$currentUserRole->isLeaderCC()) {
//						throw new ForbiddenException(
//							$translator->trans("You are not authorized to perform this action.", [], null, $request->getLocale())
//						);
//				}

        $organization = $currentUser->getOrganization();

        $expires_in = 31536000; // 1 Год
        $access_token = $JWT->makeAccessToken(["organization_id" => $organization->getId()], $expires_in);

        $this->logInfo(LogActionType::ORGANIZATION_GENERATEPERSONALACCESSTOKEN);

        return $this->json([
            "access_token" => $access_token,
            "expires_in" => $expires_in,
            "token_type" => "Bearer"
        ]);
    }
}
