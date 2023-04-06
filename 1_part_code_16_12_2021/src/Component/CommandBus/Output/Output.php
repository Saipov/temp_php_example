<?php

namespace App\Component\CommandBus\Output;

/**
 *
 */
class Output implements OutputInterface
{
    private $data = null;

    /**
     * @param $data
     */
    public function write($data): void
    {
        $this->data = $data;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }
}