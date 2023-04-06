<?php

namespace App\Component\CommandBus\Command\Contact;

use Exception;
use Psr\Log\LoggerInterface;
use App\Classes\Helper;
use App\Component\CommandBus\AbstractCommand;
use App\Component\CommandBus\CommandBusBeforeHandle;
use App\Component\CommandBus\CommandBusHandleInterface;
use App\Component\CommandBus\CommandBusValidator;
use App\Component\CommandBus\Input\InputInterface;
use App\Component\CommandBus\Output\OutputInterface;
use App\Entity as Entity;
use App\Exception\Http\InternalServerError;
use App\Repository\ContactRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Вернёт список контактов
 */
class ContactGet extends AbstractCommand implements CommandBusHandleInterface, CommandBusValidator, CommandBusBeforeHandle
{
    use MatchAgainst;

    private LoggerInterface $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \App\Component\CommandBus\Input\InputInterface $input
     *
     * @throws \App\Exception\Http\BadRequestException
     */
    public function validate(InputInterface $input)
    {
        $violations = $this->validator
            ->validate(
                $input->getParameters(),
                new Assert\Collection([
                    "fields" => [
                        "owner_id" => [
                            new Assert\Optional([
                                new Assert\Positive()
                            ])
                        ],
                        "project_id" => [
                            new Assert\Optional([
                                new Assert\Positive()
                            ])
                        ],
                        "user_group_id" => [
                            new Assert\Optional([
                                new Assert\Positive()
                            ])
                        ],
                        "q" => [
                            new Assert\Optional([
                                new Assert\Type(["string"])
                            ])
                        ],
                        "task" => [
                            new Assert\Optional([
                                new Assert\Choice([
                                    "multiple" => false,
                                    "choices" => ["available", "unavailable", "overdue", "not_overdue"]
                                ])
                            ])
                        ],
                        "calling" => [
                            new Assert\Optional([
                                new Assert\Choice([
                                    "choices" => ["yes", "no"],
                                    "multiple" => false
                                ])
                            ])
                        ],
                        "last_call_at" => [
                            new Assert\Optional([
                                new Assert\Type(["string"])
                            ])
                        ],
                        "contact_created_at" => [
                            new Assert\Optional([
                                new Assert\Type(["string"])
                            ])
                        ],
                        "tag_ids" => [
                            new Assert\Optional([
                                new Assert\Type("array")
                            ])
                        ],
                        "status_ids" => [
                            new Assert\Optional([
                                new Assert\Type(["array"])
                            ])
                        ],
                        "offset" => [
                            new Assert\Optional([
                                new Assert\Type(["string"])
                            ])
                        ],
                        "count" => [
                            new Assert\Optional([
                                new Assert\Type(["string"])
                            ])
                        ],
                        "order_by" => [
                            new Assert\Optional([
                                new Assert\Choice([
                                    "choices" => [
                                        "contact_name", "project", "created_at", "last_call_at"
                                    ],
                                    "multiple" => false
                                ])
                            ])
                        ],
                        "order_direction" => [
                            new Assert\Optional([
                                new Assert\Choice([
                                    "choices" => [
                                        "asc", "desc", "ASC", "DESC"
                                    ],
                                    "multiple" => false
                                ])
                            ])
                        ],
                        "utc_offset" => [
                            new Assert\Optional([
                                new Assert\Range([
                                    "min" => -12,
                                    "max" => 12
                                ])
                            ])
                        ]
                    ]
                ])
            );

        if (count($violations) > 0) {
            $this->throwBadRequest($violations);
        }
    }

    /**
     * @param InputInterface $input
     *
     * @return void
     * @throws \Exception
     */
    public function beforeHandle(InputInterface $input)
    {
        if ($input->hasParameter("q")) {
            if (preg_match("/^[0-9+]+/s", $input->getParameter("q"))) {
                // Если номер телефона
                $input->setParameter("q_by_phone", $input->getParameter("q"));
                $input->removeParameter("q");
            } else {
                $input->setParameter("q", $this->maEq($input->getParameter("q")));
            }
        }

        if ($input->hasParameter("contact_created_at")) {
            $input->setParameter("contact_created_at", Helper::unixtimeToDatePeriod($input->getParameter("contact_created_at")));
        }

        if ($input->hasParameter("calling")) {
            switch ($input->getParameter("calling")) {
                case "yes":
                    $input->setParameter("calling", true);
                    break;
                case "no":
                    $input->setParameter("calling", false);
            }
        }

        if (!$input->hasParameter("count")) {
            $input->setParameter("count", 50);
        }
    }

    /**
     * @param InputInterface                                   $input
     * @param \App\Component\CommandBus\Output\OutputInterface $output
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \App\Exception\Http\InternalServerError
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        /** @var ContactRepository $contactRepository */
        $contactRepository = $this->getRepository(Entity\Contact::class);

        try {
            $paginator = $contactRepository->findContacts($input->getParameters());
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage(), [
                "code" => $e->getCode(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "trace" => $e->getTrace()
            ]);
            throw new InternalServerError("Во время получения данных произошла ошибка");
        }

        $output->write([
            "meta" => [
                "count" => $paginator->count(true)
            ],
            "data" => $this->normalizer->normalize($paginator->getQuery()->getResult())
        ]);
    }
}
