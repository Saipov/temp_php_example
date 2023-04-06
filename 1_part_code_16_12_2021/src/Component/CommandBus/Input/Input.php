<?php

namespace App\Component\CommandBus\Input;

/**
 * Input
 */
class Input implements InputInterface
{
    protected array $parameters = [];

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getParameter(string $name, $default = null)
    {
        if ($this->hasParameter($name)) {
            return $this->parameters[$name];
        }

        return $default;
    }

    /**
     * @param string $name
     * @param        $value
     */
    public function setParameter(string $name, $value = null)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param string $name
     */
    public function removeParameter(string $name)
    {
        if ($this->hasParameter($name)) {
            unset($this->parameters[$name]);
        }
    }

    /**
     * @param array $arguments
     */
    public function setParameters(array $arguments)
    {
        foreach ($arguments as $name => $argument) {
            $this->setParameter($name, $argument);
        }
    }

}