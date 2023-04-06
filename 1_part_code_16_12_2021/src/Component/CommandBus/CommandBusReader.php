<?php

namespace App\Component\CommandBus;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class CommandBusReader
{
    private $data;

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @param int   $status
     * @param array $headers
     * @param bool  $json
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function jsonResponse(int $status = 200, array $headers = [], bool $json = false): JsonResponse
    {
        return new JsonResponse($this->data, $status, $headers, $json);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}