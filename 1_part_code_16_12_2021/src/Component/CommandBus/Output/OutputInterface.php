<?php

namespace App\Component\CommandBus\Output;

/**
 * OutputInterface
 */
interface OutputInterface
{
    /**
     * @param $data
     */
    public function write($data): void;
}