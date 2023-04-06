<?php

namespace App\Component\CommandBus;


use App\Component\CommandBus\Input\Input;
use App\Component\CommandBus\Output\Output;

/**
 * CommandBusHandler
 */
class CommandBusHandler
{
    private CommandBusHandleInterface $busHandle;
    private Input $input;
    private Output $output;

    /**
     * @param CommandBusHandleInterface $busHandle
     */
    public function __construct(CommandBusHandleInterface $busHandle)
    {
        $this->busHandle = $busHandle;
        $this->output = new Output();
    }

    /**
     * @param \App\Component\CommandBus\Input\Input $input
     *
     * @return $this
     */
    public function setup (Input $input): CommandBusHandler
    {
        $this->input = $input;

        return $this;
    }

    /**
     * @return \App\Component\CommandBus\CommandBusHandler
     */
    public function validate (): CommandBusHandler
    {
        if ($this->busHandle instanceof CommandBusValidator) {
            $this->busHandle->validate($this->input ?? $this->input = new Input());
        }

        return $this;
    }

    /**
     * @return \App\Component\CommandBus\CommandBusReader
     */
    public function handle(): CommandBusReader
    {
        // Before handle
        if (method_exists($this->busHandle, "beforeHandle")) {
            $this->busHandle->beforeHandle($this->input ?? $this->input = new Input());
        }

        try {
            $this->busHandle->handle($this->input ?? $this->input = new Input(), $this->output);
            return new CommandBusReader($this->output->getData());
        } finally {
            if (method_exists($this->busHandle, "finallyHandle")) {
                $this->busHandle->finallyHandle();
            }
        }
    }
}