<?php


namespace App\Component\CommandBus\Command\Contact;


use App\Classes\Helper;
use App\Component\CommandBus\AbstractCommand;
use App\Component\CommandBus\CommandBusBeforeHandle;
use App\Component\CommandBus\CommandBusHandleInterface;
use App\Component\CommandBus\CommandBusValidator;
use App\Component\CommandBus\Input\InputInterface;
use App\Component\CommandBus\Output\OutputInterface;
use App\Exception\Http\BadRequestException;
use App\Message\ExportContact;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 *
 */
class ContactExport
    extends AbstractCommand
    implements CommandBusHandleInterface, CommandBusValidator, CommandBusBeforeHandle
{
    use MatchAgainst;

    private MessageBusInterface $messageBus;

    /**
     * @param MessageBusInterface $messageBus
     */
    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     *
     * @param InputInterface $input
     * @return void
     * @throws BadRequestException
     */
    public function validate(InputInterface $input)
    {
        $violations = $this->validator
            ->validate(
                $input->getParameters(),
                new Assert\Collection([
                    "fields" => [
                        "ids" => [
                            new Assert\Optional(

                            )
                        ],
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
                        ],
                        "format" => [
                            new Assert\Required(),
                            new Assert\Choice([
                                "choices" => ["xls", "xlsx", "ods", "csv", "html", "tcpdf", "dompdf", "mpdf"],
                                "multiple" => false
                            ])
                        ],
                    ]
                ])
            );


        if (count($violations) > 0) {
            $this->throwBadRequest($violations);
        }
    }

    /**
     * @param \App\Component\CommandBus\Input\InputInterface $input
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
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        $message = new ExportContact(
            $this->paramsCasting($input),                       // параметры запроса в базу данных.
            $this->getUser()->getId(),                          // Пользователь от имени которого запускается задача.
            $input->getParameter("format", "xlsx")  // Формат
        );

        $this->messageBus->dispatch($message);
    }

    /**
     * Выбирает все параметры которые имеют отношение к выборке из базы данных.
     *
     * @param \App\Component\CommandBus\Input\InputInterface $input
     *
     * @return array Массив параметров.
     */
    private function paramsCasting(InputInterface $input): array
    {
        $permitted = [
            "ids",
            "owner_id",
            "project_id",
            "user_group_id",
            "q",
            "q_by_phone",
            "task",
            "calling",
            "last_call_at",
            "contact_created_at",
            "tag_ids",
            "status_ids",
            "timezone",
            "order_by",
            "order_direction",
            "utc_offset",
        ];

        return array_filter($input->getParameters(), function ($e) use ($permitted, $input) {
            return in_array($e, $permitted);
        }, ARRAY_FILTER_USE_KEY);
    }
}
