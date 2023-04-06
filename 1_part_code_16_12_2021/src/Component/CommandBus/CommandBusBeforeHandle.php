<?php

namespace App\Component\CommandBus;

use App\Component\CommandBus\Input\InputInterface;

/**
 * CommandBusBeforeHandle
 */
interface CommandBusBeforeHandle
{
    /**
     * @param InputInterface $input
     * @return void
     */
    public function beforeHandle(InputInterface $input);
}