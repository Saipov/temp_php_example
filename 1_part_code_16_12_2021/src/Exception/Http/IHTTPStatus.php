<?php


namespace App\Exception\Http;

/**
 * Interface IHTTPStatus
 *
 * @package App\Exception\Http
 */
interface IHTTPStatus
{
    public function getErrorMessage();

    public function getErrorCode();

    public function getHttpStatusCode();

    public function getErrors();
}
