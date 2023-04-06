<?php


namespace App\EventSubscriber;


use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity as Entity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Core\Security;

/**
 * Class KernelControllerSubscriber
 *
 * @package App\EventSubscriber
 */
class KernelControllerSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    /**
     * KernelController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param Security               $security
     */
    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;

    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ControllerEvent::class => 'onKernelController',
        ];
    }

    /**
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event)
    {
        $user = $this->security->getUser();
        if ($user instanceof Entity\User) {
            if ($user->getTimezone() instanceof DateTimeZone) {
                // Устанавливаю часовой пояс по умолчанию, используемый всеми функциями
                // даты / времени в скрипте.
                date_default_timezone_set($user->getTimezone()->getName());
            }
        }
    }
}