<?php

namespace App\Component\CommandBus\Command\Contact;

use App\Component\CommandBus\AbstractCommand;
use App\Component\CommandBus\CommandBusBeforeHandle;
use App\Component\CommandBus\CommandBusHandleInterface;
use App\Component\CommandBus\CommandBusValidator;
use App\Component\CommandBus\Input\InputInterface;
use App\Component\CommandBus\Output\OutputInterface;
use App\Entity as Entity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class ContactFindByPhoneNumber extends AbstractCommand implements
    CommandBusBeforeHandle,
    CommandBusHandleInterface,
    CommandBusValidator
{

    /**
     * @param \App\Component\CommandBus\Input\InputInterface $input
     *
     * @return mixed|void
     */
    public function beforeHandle(InputInterface $input)
    {
        if ($input->hasParameter("order_by") && $input->hasParameter("order_direction")) {
            $sort = "";
            switch ($input->getParameter("order_by")) {
                // Сортировка по имени контакта
                case "by_name":
                {
                    $sort = "full_name";
                    break;
                }
                // Сортировка по дате последнего звонка
                case "by_last_call_at": {
                    $sort = "c.last_call_at";
                    break;
                }
                // Сортировка по дате создания контакта
                case "by_created_at": {
                    $sort = "c.created_at";
                    break;
                }
                // Сортировка по проектам
                case "by_project": {
                    $sort = "project_id";
                    break;
                }
            }
            $input->setParameter("order_by", [
                $sort => $input->getParameter("order_direction")
            ]);
        }
    }

    /**
     * @param \App\Component\CommandBus\Input\InputInterface   $input
     * @param \App\Component\CommandBus\Output\OutputInterface $output
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        /** @var \App\Repository\ContactRepository $contactRepository */
        $contactRepository = $this->getRepository(Entity\Contact::class);

        // Поиск только по номеру
        $paginator = $contactRepository
            ->findByPhoneNumber(
                $input->getParameter("q"),
                $input->getParameter("offset", 0),
                $input->getParameter("count", 50),
                $input->getParameter("order_by", [])
        );

        $output->write([
            "meta" => [
                "type" => "array",
                "count" => $paginator->count()
            ],
            "data" => $this->normalizer->normalize($paginator->getQuery()->getResult())
        ]);
    }

    /**
     * @param \App\Component\CommandBus\Input\InputInterface $input
     *
     * @return void
     * @throws \App\Exception\Http\BadRequestException
     */
    public function validate(InputInterface $input)
    {
        $violations = $this->validator
            ->validate(
                $input->getParameters(),
                new Assert\Collection([
                    "fields" => [
                        "q" => [
                            new Assert\NotBlank(),
                        ],
                        "order_by" => [
                            new Assert\Optional([
                                new Assert\Type(["string"])
                            ])
                        ],
                        "order_direction" => [
                            new Assert\Optional([
                                new Assert\Type(["string"])
                            ])
                        ],
                    ]
                ])
            );

        if (count($violations) > 0) {
            $this->throwBadRequest($violations);
        }
    }
}