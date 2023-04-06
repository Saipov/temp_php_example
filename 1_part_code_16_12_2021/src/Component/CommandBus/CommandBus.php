<?php

namespace App\Component\CommandBus;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * CommandBus
 */
class CommandBus implements ServiceSubscriberInterface
{
    private ContainerInterface $locator;
    private static $services = [];

    /**
     * @param ContainerInterface  $locator
     */
    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->locator->has($id);
    }

    /**
     * @param string $id
     *
     * @return CommandBusHandler
     */
    public function get(string $id): CommandBusHandler
    {
        return new CommandBusHandler($this->locator->get($id));
    }

    /**
     * Зарегистрируй команды.
     *
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
        foreach (get_declared_classes() as $className) {
            if (in_array(CommandBusHandleInterface::class, class_implements($className))) {
                $names = explode("\\", $className);
                self::$services[$names[array_key_last($names)]] = $className;
            }
        }

        return self::$services;
    }
}