<?php


namespace App\Component\CommandBus\Command\Contact;


use App\Component\CommandBus\AbstractCommand;
use App\Component\CommandBus\CommandBusBeforeHandle;
use App\Component\CommandBus\CommandBusHandleInterface;
use App\Component\CommandBus\CommandBusValidator;
use App\Component\CommandBus\Input\InputInterface;
use App\Component\CommandBus\Output\OutputInterface;
use App\Exception\Http\BadRequestException;
use App\Message\ImportContact;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 *
 */
class ContactImport
    extends AbstractCommand
    implements CommandBusHandleInterface, CommandBusValidator, CommandBusBeforeHandle
{

    private MessageBusInterface $messageBus;
    private string $projectDir;
    private Filesystem $filesystem;

    /**
     * @param MessageBusInterface                           $messageBus
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function __construct(
        MessageBusInterface $messageBus,
        KernelInterface     $kernel,
        Filesystem          $filesystem
    )
    {
        $this->messageBus = $messageBus;
        $this->projectDir = $kernel->getProjectDir();
        $this->filesystem = $filesystem;
    }

    /**
     *
     * @param InputInterface $input
     *
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
                        "file" => [
                            new Assert\File([
                                "maxSize" => ini_get("upload_max_filesize"),
                                "mimeTypes" => [
                                    "application/vnd.ms-excel",
                                    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                                    "text/csv"
                                ]
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
        //
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $input->getParameter("file");
        $fileName = uniqid("import_") . "." . $file->guessExtension();

        $destinationPath = sprintf("%s/data/imports/contacts", $this->projectDir);
        if (!$this->filesystem->exists($destinationPath)) {
            $this->filesystem->mkdir($destinationPath);
        }

        try {
            $file->move($destinationPath, $fileName);
        } catch (FileException $e) {
            throw new FileException($e->getMessage());
        }

        $this->messageBus
            ->dispatch(new ImportContact(
                "$destinationPath/$fileName",
                $this->getUser()->getId()
            ));
    }
}
