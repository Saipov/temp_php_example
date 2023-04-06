<?php


namespace App\EventListener\Exception;

use App\Exception\HTTP\IHTTPStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

/**
 * Class ExceptionListener
 *
 * @package App\Listeners\Exception
 */
class ExceptionListener implements EventSubscriberInterface
{
    private array $headers = [];
    private TranslatorInterface $translator;
    private ?Request $request;

    /**
     * ExceptionListener constructor.
     *
     * @param TranslatorInterface $translator
     * @param RequestStack        $requestStack
     * @param KernelInterface     $kernel
     */
    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, KernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->request = $requestStack->getCurrentRequest();

        if ($kernel->getEnvironment() == "dev") {
            $this->headers = [
                "Access-Control-Allow-Credentials" => "true",
                "Access-Control-Allow-Origin" => $_ENV["CORS_ALLOW_ORIGIN"] ?? "*",
                "Access-Control-Expose-Headers" => "*"
            ];
        }else {
            $this->headers = array_merge($this->request->headers->all(), [
                "Access-Control-Allow-Origin" => $_ENV["CORS_ALLOW_ORIGIN"] ?? "*"
            ]);
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException'],
        ];
    }


    /**
     * @param ExceptionEvent $event
     *
     * @return void
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // API HTTP STATUS
        if ($exception instanceof IHTTPStatus) {
            $event->setResponse($this->handleHttpStatus($event, $exception));
            return;
        }

        // HTTP NOT FOUND
        if ($exception instanceof NotFoundHttpException) {
            $event->setResponse($this->handleHttpNotFound($event, $exception));
            return;
        }

        // HTTP NOT FOUND
        if ($exception instanceof AccessDeniedHttpException) {
            $event->setResponse($this->handleHttpAccessDenied($event, $exception));
            return;
        }

        $event->setResponse($this->handleException($event, $exception));
    }

    /**
     * @param ExceptionEvent $event
     * @param IHTTPStatus    $exception
     *
     * @return JsonResponse
     */
    private function handleHttpStatus(ExceptionEvent $event, IHTTPStatus $exception): JsonResponse
    {
        return new JsonResponse([
            "error_code" => $exception->getErrorCode(),
            "error_message" => $this->translator->trans($exception->getErrorMessage(), [], null, $this->request->getLocale()),
            "errors" => $exception->getErrors(),
        ], $exception->getHttpStatusCode(), $this->headers);
    }

    /**
     * @param ExceptionEvent $event
     * @param Throwable      $exception
     */
    private function handleHttpNotFound(ExceptionEvent $event, Throwable $exception): JsonResponse
    {
        if ($_SERVER['APP_ENV'] == "dev") {
            $for_dev = [
                "debug_message" => $exception->getMessage(),
                "debug_file" => $exception->getFile(),
                "debug_line" => $exception->getLine(),
                "debug_stack_trace" => $exception->getTrace(),
            ];
        }

        return new JsonResponse(array_merge([
            "error_code" => "http_not_found",
            "error_message" => "Передан неизвестный метод",
        ], $for_dev), Response::HTTP_NOT_FOUND, $this->headers);
    }

    /**
     * @param ExceptionEvent            $event
     * @param AccessDeniedHttpException $exception
     *
     * @return JsonResponse
     */
    private function handleHttpAccessDenied(ExceptionEvent $event, AccessDeniedHttpException $exception): JsonResponse
    {
        return new JsonResponse([
            "error_code" => "access_denied",
            "error_message" => $this->translator->trans($exception->getMessage(), [], null, $this->request->getLocale()),
        ], $exception->getStatusCode(), $this->headers);
    }

    /**
     * @param ExceptionEvent $event
     * @param Throwable      $exception
     *
     * @return JsonResponse
     */
    private function handleException(ExceptionEvent $event, Throwable $exception): JsonResponse
    {
        $for_dev = [];

        if ($_SERVER['APP_ENV'] == "dev") {
            $for_dev = [
                "debug_message" => $exception->getMessage(),
                "debug_file" => $exception->getFile(),
                "debug_line" => $exception->getLine(),
                "debug_stack_trace" => $exception->getTrace(),
            ];
        }

        return new JsonResponse(array_merge([
            "error_code" => "unknown_error",
            "error_message" => "Неизвестная ошибка",
            "class" => get_class($exception),
        ], $for_dev), 520, $this->headers);
    }
}
