<?php

namespace App\Component\CommandBus;

use App\Component\CommandBus\Input\InputInterface;

/**
 * CommandBusValidator
 */
interface CommandBusValidator
{
    /**
     * @param InputInterface $input
     */
    public function validate(InputInterface $input);
}