<?php

namespace App\Component\CommandBus\Command\UserGroup;

use App\Component\CommandBus\AbstractCommand;
use App\Component\CommandBus\CommandBusHandleInterface;
use App\Component\CommandBus\CommandBusValidator;
use App\Component\CommandBus\Input\InputInterface;
use App\Component\CommandBus\Output\OutputInterface;
use App\Entity as Entity;
use App\Exception\Http\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Возвращает список групп пользователей.
 *
 * UserGroupGet
 */
class UserGroupGet extends AbstractCommand implements CommandBusHandleInterface, CommandBusValidator
{
    public function beforeHandle(InputInterface $input)
    {
        if ($input->hasParameter("group_ids")) {
            $matches = preg_split("/[\s+,]+/s", $input->getParameter("group_ids"));
            if ($matches) {
                $input->setParameter("group_ids", array_map(function ($e) { return (integer)$e; }, $matches));
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws ExceptionInterface
     */
    public function handle(InputInterface $input, OutputInterface $output): void
    {
        $userGroupRepository = $this->getRepository(Entity\UserGroup::class);

        if ($input->hasParameter("group_ids")) {
            $groups = $userGroupRepository->findBy(["id" => $input->getParameter("group_ids")]);
            $groupsCount = count($groups);
        } else {
            $result = $userGroupRepository->findGroups($input->getParameters());
            $groups = $result->getQuery()->getResult();
            $groupsCount = $result->count();
        }


        $groupsNormalized = $this->normalizer->normalize($groups);
        $output->write([
            "meta" => [
                "type" => "array",
                "count" => $groupsCount
            ],
            "data" => $groupsNormalized
        ]);
    }

    /**
     * Здесь опиши всё, что необходимо проверить до того как данные будут переданы в исполнитель
     *
     * @param InputInterface $input
     *
     * @return void
     * @throws BadRequestException
     */
    public function validate(InputInterface $input): void
    {
        $violations =  $this->validator
            ->validate(
                $input->getParameters(),
                new Assert\Collection(
                    [
                        "q" => [
                            new Assert\Optional([
                                new Assert\Type("string")
                            ]),
                        ],
                        "offset" => [
                            new Assert\Optional([
                                new Assert\PositiveOrZero()
                            ]),
                        ],
                        "count" => [
                            new Assert\Optional([
                                new Assert\Positive()
                            ]),
                        ],
                        "group_ids" => [
                            new Assert\Optional([
                                new Assert\Regex(["pattern" => "/^[\d+,\s+]+$/"])
                            ]),
                        ]
                    ]
                ));
        if (count($violations) > 0) {
            $this->throwBadRequest($violations);
        }
    }
}