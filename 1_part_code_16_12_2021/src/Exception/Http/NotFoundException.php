<?php


namespace App\Exception\Http;


use Exception;

/**
 * Class NotFoundException
 *
 * @package App\Exception\Http
 */
class NotFoundException extends Exception implements IHTTPStatus
{
    const STATUS_CODE = 404;

    private $error_message;
    private $error_code;
    private $errors;

    /**
     * NotFoundException constructor.
     *
     * @param       $error_message
     * @param       $error_code
     * @param array $errors
     * @param       $previous
     */
    public function __construct($error_message, $error_code = "not_found", $errors = [], $previous = null)
    {
        parent::__construct($error_message, 0, $previous);

        $this->error_message = $error_message;
        $this->error_code = $error_code;
        $this->errors = $errors;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return self::STATUS_CODE;
    }

    /**
     * @return array|null
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
