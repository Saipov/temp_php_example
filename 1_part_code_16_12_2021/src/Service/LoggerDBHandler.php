<?php


namespace App\Service;


use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Monolog\Handler\AbstractProcessingHandler;
use App\Entity\Log;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class LoggerDBHandler extends AbstractProcessingHandler
{
    private EntityManagerInterface $entityManager;

    private Security $security;

    /**
     * LoggerDBHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param Security               $security
     */
    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @param array $record
     *
     * @throws Exception
     */
    protected function write(array $record): void
    {
        $user = $this->security->getUser();

        $data = new Log();
        $data->setAction($record["message"]);

        if ($user instanceof User) {
            $data->setUser($user);
        }

        $data->setStartActionAt(new DateTime($record["datetime"]));
        $data->setContext($record["context"]);
        $data->setIp($record["extra"]["ip"]);
        $data->setUrl($record["extra"]["url"]);
        $data->setHttpMethod($record["extra"]["http_method"]);
        $data->setController($record["extra"]["requests"][0]["controller"]);

        // Простым способом отсекаем передачу пароля
        if ($record["extra"]["requests"][0]["controller"] === "App\Controller\App\AccountController::authorization") {
            unset($record["extra"]["request"]["password"]);
            $data->setRequest($record["extra"]["request"]);
        } else {
            $data->setRequest($record["extra"]["request"]);
        }

        $data->setUserAgent($record["extra"]["user-agent"]);
        $data->setQueryString($record["extra"]["query_string"]);

        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }
}
