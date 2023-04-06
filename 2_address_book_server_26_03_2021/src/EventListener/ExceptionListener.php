<?php

namespace App\EventListener;


use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class ExceptionListener
 * @package App\Listener
 * @TODO Обработать правильно коды ошибок от формы
 */
class ExceptionListener
{
    use LoggerAwareTrait;

    public function onKernelException(ExceptionEvent $event)
    {
        $throwable = $event->getThrowable();
        $type = get_class($throwable);

        if (method_exists($throwable, 'getStatusCode')) {
            $statusCode = $throwable->getStatusCode();
        } else {
            switch ($type) {
                case 'Symfony\Component\Routing\Exception\ResourceNotFoundException':
                    $statusCode = Response::HTTP_NOT_FOUND;
                    break;

                case 'Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException':
                    $statusCode = Response::HTTP_NOT_IMPLEMENTED;
                    break;

                default:
                    $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                    break;
            }
        }

        $error = [];
        switch ($type) {

            case 'App\Exception\FormException':
                $data = [];
                $errors = $throwable->getErrors();
                foreach ($errors as $e) {
                    $data[$e->getOrigin()->getName()] = $e->getMessage();
                }

                $error['form'] = $data;
                break;

            default:
                $message = $throwable->getMessage();
                if (gettype($message) == 'string') {
                    $error['detail'] = $message;
                } else {
                    $error = $message;
                }
                break;
        }


        $content = $this->format($statusCode, $error);

        $response = new Response();
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', 'application/json');
        $jsonContent = json_encode($content);

        $response->setContent($jsonContent);
        $event->setResponse($response);
    }

    private function format(int $code, $errors = '')
    {

        $response = [
            'code' => $code,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return $response;
    }
}