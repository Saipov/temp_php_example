<?php

namespace App\Controller\App;

use Psr\Log\LoggerInterface;
use App\Classes\AbstractController;
use App\Classes\Helper;
use App\DBAL\LogActionType;
use App\Entity\Log;
use App\Exception\Http\BadRequestException;
use App\Exception\Http\NotFoundException;
use App\Repository\LogRepository;
use App\Validator\PropertyValidator\Constraints\LogScheme;
use App\Validator\PropertyValidator\PropertyValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class LogController
 *
 * @package App\Controller\App
 * @Route("/logs")
 */
class LogController extends AbstractController
{

    /**
     * @Route(
     *     path="/",
     *     methods={"GET"}
     * )
     * @param Request             $request
     * @param LogRepository       $logRepository
     * @param NormalizerInterface $normalizer
     * @param TranslatorInterface $translator
     * @param PropertyValidator   $propertyValidator
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function getLogs(
        Request $request,
        LogRepository $logRepository,
        NormalizerInterface $normalizer,
        TranslatorInterface $translator,
        PropertyValidator $propertyValidator
    ): JsonResponse
    {

        // Проверяю входящие данные
        $propertyValidator->validate($request->request->all(), LogScheme::class);

        $period = Helper::periodOfTheCurrentDay(); // Период текущего дня.
        if ($request->query->has("date_period")) {
            $period = Helper::unixtimeToDatePeriod($request->get("date_period"));
        }

        $logCreatedAt = null;
        if ($request->query->has("log_created_at")) {
            $logCreatedAt = $request->get("log_created_at", false);
            if ($logCreatedAt) {
                $logCreatedAt = Helper::unixtimeToDatePeriod($logCreatedAt);
            }
        }

        $logs = $logRepository->findByActionAndDetails([
            "creator_id" => $request->get("creator_id", 0),
            "action" => $request->get("action", ""),
            "http_method" => $request->get("http_method", ""),
            "log_created_at" => $logCreatedAt,
            "period" => $period,
            "offset" => $request->get("offset", 0),
            "count" => $request->get("count", 100),
        ]);

        $logsNormalized = $normalizer->normalize($logs->getQuery()->getResult(), "json", [AbstractNormalizer::ATTRIBUTES => [
            "id",
            "action",
            "ip",
            "userAgent",
            "startActionAt",
            "user" => ["first_name", "last_name"]],
            AbstractNormalizer::CALLBACKS => [
                // Названия действий в нормально виде
                "action" => function ($inner) use ($translator) {
                    return $translator->trans($inner);
                }]
        ]);

        return $this->json([
            "meta" => [
                "count" => $logs->count()
            ],
            "data" => $logsNormalized
        ]);
    }

    /**
     *
     * Список действий для фильтра
     *
     * @Route(
     *     path="/actions",
     *     methods={"GET"}
     * )
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function getActionsLog(
        TranslatorInterface $translator): JsonResponse
    {
        $actionsLog = LogActionType::toArray();
        return $this->json([
            "data" => array_map(function ($e) use ($translator) {
                return [
                    "title" => $translator->trans($e),
                    "type" => $e
                ];
            }, $actionsLog)
        ]);
    }

    /**
     *
     * @Route(
     *     path="/{id}",
     *     requirements={"id": "\w+"},
     *     methods={"GET"}
     * )
     * @param string              $id
     * @param LogRepository       $logRepository
     * @param NormalizerInterface $normalizer
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     * @throws ExceptionInterface
     * @throws NotFoundException
     */
    public function getLogById(
        string $id,
        LogRepository $logRepository,
        NormalizerInterface $normalizer,
        TranslatorInterface $translator
    ): JsonResponse
    {
        $log = $logRepository->find($id);

        if (!$log instanceof Log) {
            throw new NotFoundException("Log not found.", "log_not_found");
        }

        $logNormalized = $normalizer->normalize($log, "json", [
            AbstractNormalizer::CALLBACKS => [
                "action" => function ($inner) use ($translator) {
                    return $translator->trans($inner);
                }]
        ]);


        return $this->json($logNormalized);
    }

}
