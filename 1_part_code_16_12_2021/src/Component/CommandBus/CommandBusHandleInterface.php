<?php

namespace App\Component\CommandBus;

use App\Component\CommandBus\Input\InputInterface;
use App\Component\CommandBus\Output\OutputInterface;

/**
 * CommandBusHandleInterface
 */
interface CommandBusHandleInterface
{
    /**
     * @return void
     */
    public function handle(InputInterface $input, OutputInterface $output);
}