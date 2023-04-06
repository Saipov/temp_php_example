<?php

namespace App\Repository;

use DatePeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use App\DBAL\LogActionType;
use App\Entity\Log;

/**
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }


    /**
     * @param string $action
     * @param array  $params
     * @param int    $offset
     * @param int    $count
     *
     * @return Paginator
     */
    public function findByActionAndDetails(array $params = []): Paginator
    {
        $creator_id = key_exists("creator_id", $params) ? (int)$params["creator_id"] : 0;
        $action = key_exists("action", $params) ? (string)$params["action"] : null;
        $http_method = key_exists("http_method", $params) ? (string)$params["http_method"] : null;
        $log_created_at = key_exists("log_created_at", $params) ? $params["log_created_at"] : null;
        $period = key_exists("period", $params) ? $params["period"] : null;
        $offset = key_exists("offset", $params) ? (int)$params["offset"] : 0;
        $count = key_exists("count", $params) ? (int)$params["count"] : 0;

        $qb = $this->createQueryBuilder("t");

        // Фильтрация по периоду
        if ($period instanceof DatePeriod) {
            $qb = $qb
                ->andWhere($qb
                    ->expr()
                    ->between("t.start_action_at", ":start_created_at", ":end_created_at")
                );

            $qb = $qb->setParameter("start_created_at", $period->getStartDate());
            $qb = $qb->setParameter("end_created_at", $period->getEndDate());
        }


        // Фильтрация по дате создания лога
        if ($log_created_at instanceof DatePeriod) {
            $qb = $qb
                ->andWhere($qb
                    ->expr()
                    ->between("t.start_action_at", ":start_created_at", ":end_created_at")
                );

            $qb = $qb->setParameter("start_created_at", $log_created_at->getStartDate());
            $qb = $qb->setParameter("end_created_at", $log_created_at->getEndDate());
        }

        // Создатель лог записи
        if ($creator_id > 0) {
            $qb = $qb->andWhere("t.user = :creator_id");
            $qb = $qb->setParameter("creator_id", $creator_id);
        }

        // Фильтрация по действию
        if (!empty($action)) {
            $qb = $qb->andWhere("t.action = :type");
            $qb = $qb->setParameter("type", LogActionType::convert($action));
        }

        // Фильтрация по HTTP методу
        if (!empty($http_method)) {
            $qb = $qb->andWhere("t.http_method = :http_method");
            $qb = $qb->setParameter("http_method", $http_method);
        }
//
//        if (!empty($params)) {
//            foreach ($params as $key => $value) {
//                $qb = $qb
//                    ->andWhere("JSON_EXTRACT(t.context, '$.{$key}') = :{$key}")
//                    ->setParameter("{$key}", $value);
//            }
//        }

        $qb = $qb->orderBy("t.id", "DESC");
        $qb = $qb->setFirstResult($offset);
        $qb = $qb->setMaxResults($count);
        $qb = $qb->setLifetime(24 * 3600);

        return new Paginator($qb->getQuery());
    }
}
