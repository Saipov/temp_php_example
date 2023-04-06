<?php


namespace App\Component\CommandBus\Command\Contact;


use Exception;
use Psr\Log\LoggerInterface;
use App\Component\CommandBus\AbstractCommand;
use App\Component\CommandBus\CommandBusHandleInterface;
use App\Component\CommandBus\CommandBusValidator;
use App\Component\CommandBus\Input\InputInterface;
use App\Component\CommandBus\Output\OutputInterface;
use App\Entity as Entity;
use App\Exception\Http\InternalServerError;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class ContactGetQueueList extends AbstractCommand
    implements CommandBusHandleInterface, CommandBusValidator
{
    private LoggerInterface $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @param InputInterface $input
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
                        "owner_id" => [
                            new Assert\Required(),
                            new Assert\Positive(),
                        ],
                        "project_id" => [
                            new Assert\Required(),
                            new Assert\Positive(),
                        ],
                        "offset" => [
                            new Assert\Optional([
                                new Assert\PositiveOrZero(),
                            ])
                        ],
                        "count" => [
                            new Assert\Optional([
                                new Assert\Positive(),
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
     * @param InputInterface                                   $input
     * @param \App\Component\CommandBus\Output\OutputInterface $output
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \App\Exception\Http\InternalServerError
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        /** @var \App\Repository\ContactsQueueRepository $contactQueRepository */
        $contactQueRepository = $this->getRepository(Entity\ContactsQueue::class);

        try {
            // Получить список контактов очереди.
            $paginator = $contactQueRepository
                ->fetch(
                    project_id: (int)$input->getParameter("project_id", 0),
                    owner_id: (int)$input->getParameter("owner_id", 0),
                    offset: (int)$input->getParameter("offset", 0),
                    count: (int)$input->getParameter("count", 0)
                );
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage(), [
                "code" => $e->getCode(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "trace" => $e->getTrace()
            ]);
            throw new InternalServerError("При получении списка произошла ошибка!");
        }

        $output->write([
            "meta" => [
                "count" => $paginator->count()
            ],
            "data" => $this->normalizer->normalize($paginator->getQuery()->getResult())
        ]);
    }
}
