<?php

namespace App\Component\CommandBus\Input;

/**
 *
 */
interface InputInterface
{
    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getParameter(string $name, $default = null);

    /**
     * @param string $name
     * @param null   $value
     *
     * @return mixed
     */
    public function setParameter(string $name, $value);
    public function removeParameter(string $name);
    public function hasParameter(string $name): bool;
    public function getParameters(): array;
}