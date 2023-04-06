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
use App\Message\DeleteContacts;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 *
 */
class ContactDelete
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
                            new Assert\Optional()
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
        $message = new DeleteContacts(
            $input->getParameters(),
            $this->getUser()->getId(),
        );

        $this->messageBus->dispatch($message);
    }
}
