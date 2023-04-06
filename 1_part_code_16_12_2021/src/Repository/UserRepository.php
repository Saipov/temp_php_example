<?php

namespace App\Repository;

use App\DBAL\DialerStateType;
use App\DBAL\UserModeType;
use App\DTO\ParamsGetUsers;
use App\Entity as Entity;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Entity\User|null find($id, $lockMode = null, $lockVersion = null)
 * @method Entity\User|null findOneBy(array $criteria, array $orderBy = null)
 * @method Entity\User[]    findAll()
 * @method Entity\User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{

    /**
     * UserRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entity\User::class);
    }

    /**
     * @param ParamsGetUsers $params
     *
     * @return Paginator
     */
    public function getUsers (ParamsGetUsers $params): Paginator
    {
        // Сразу на DTO
        $dto = "NEW App\DTO\UserDTO(
                    u.id, 
                    u.first_name, 
                    u.last_name, 
                    u.middle_name, 
                    u.last_activity_at,
                    project.id, 
                    project.name,
                    user_group.id,
                    user_group.name,
                    u.dialer_state,
                    u.online
                )";

        $qb = $this->createQueryBuilder("u")
            ->select($dto)
            ->leftJoin("u.project", "project")
            ->leftJoin("u.group", "user_group");

        // Полнотекстовый поиск (люблю эксперименты)
        if (!empty($params->q)) {
            $qb = $qb->andWhere("MATCH(u.first_name, u.last_name, u.middle_name, u.login) AGAINST(:q IN BOOLEAN MODE) > 1");
            $qb = $qb->setParameter("q", "*$params->q*");
        }

        // Фильтрация по проекту
        if ($params->project_id > 0) {
            $qb = $qb->andWhere("u.project = :project_id");
            $qb = $qb->setParameter("project_id", $params->project_id);
        }

        if ($params->user_group_id > 0) {
            $qb = $qb->andWhere("u.group = :group_id");
            $qb = $qb->setParameter("group_id", $params->user_group_id);
        }

        // По умолчанию сортируем по дате последнего доступа к системе.
        $qb = $qb->addOrderBy("u.last_activity_at", "DESC");

        $paginator = new Paginator($qb->getQuery());
        $paginator->setUseOutputWalkers(false);

        return $paginator;
    }

    /**
     * Возвращает количество пользователей онлайн.
     *
     *
     * @return int
     */
    public function getOnlineCount (): int
    {
        $qb = $this->createQueryBuilder("u");
        $qb = $qb->select($qb->expr()->count("u.id"));

        $qb = $qb->andWhere($qb
            ->expr()
            ->between("u.last_activity_at", ":from", ":to"))
            ->setParameter("from", (new DateTime())->sub(DateInterval::createFromDateString("1 minute")))
            ->setParameter("to", new DateTime());

        try {
            return $qb->getQuery()
                ->enableResultCache(600)
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * Получит список пользователей системы.
     * -------------------------------------
     * <code>
     * [
     *  "q"               => Поиск по тексту (По умолчанию "").
     *  "group_id"        => Идентификатор группы (По умолчанию 0).
     *  "project_id"      => Идентификатор проекта (По умолчанию 0).
     *  "roles"           => Массив идентификаторов ролей (По умолчанию []).
     *  "role_use"        => Назначение роли (По умолчанию []).
     *  "order_by"        => Сортировка.
     *                       u.first_name
     *                       u.last_name
     *                       project.name
     *  "offset"          => Смещение относительно первого найденного элемента.
     *                       для выборки определённых подмножеств (По умолчанию 0).
     *  "count"           => Количество возвращаемых элементов, но не более 1000 (По умолчанию 100).
     *  "hydration_mode"  => Режим гидратации, (По умолчанию HYDRATE_OBJECT).
     *  "calculate_count" => Подсчёт количества на основе параметров WHERE
     *  "lifetime"        => Время жизни кэша второго уровня.
     * ]
     * </code>
     *
     * @param array $params
     * @deprecated
     * @return Paginator
     */
    public function findUsers(array $params = []): Paginator
    {
        $project_id = key_exists("project_id", $params) ? (int)$params["project_id"] : 0;
        $group_id = key_exists("group_id", $params) ? (int)$params["group_id"] : 0;
        $lifetime = key_exists("lifetime", $params) ? $params["lifetime"] : 0;
        $offset = key_exists("offset", $params) ? $params["offset"] : 0;
        $count = key_exists("count", $params) ? $params["count"] : 100;

        $order_by = key_exists("order_by", $params) ? (array)$params["order_by"] : [];

        $qb = $this->createQueryBuilder("u");

        if ($group_id > 0) {
            $qb = $qb->andWhere("u.group = :group_id")
                ->setParameter("group_id", $group_id);
        } elseif ($group_id < 0) {
            $qb = $qb->andWhere("u.group = :group_id")
                ->setParameter("group_id", $group_id);
        }

        if ($project_id > 0) {
            $qb = $qb->leftJoin("u.projects", "projects");
            $qb = $qb->andWhere("projects.id = :project_id")
                ->setParameter("project_id", $project_id);
        }

        // Полнотекстовый поиск.
        if (!empty($params["q"])) {
            $q = $params["q"];
            $qb = $qb
                ->andWhere("MATCH(u.first_name, u.last_name, u.middle_name, u.login) AGAINST(:q IN BOOLEAN MODE) > 1")
                ->setParameter("q", "*$q*");
        }

        $qb = $qb->setFirstResult($offset);
        $qb = $qb->setMaxResults($count);

        // Кэш второго уровня. (Опционально)
        $qb = $qb->setLifetime($lifetime);

        // Сортировка
        $qb = $qb->addOrderBy("u.last_activity_at", "DESC");

        return new Paginator($qb->getQuery());
    }

    /**
     * Найти пользователей по их идентификаторам
     *
     * @param array $ids Действительные идентификаторы пользователя
     *
     * @return Paginator
     */
    public function findUsersByIds(array $ids): Paginator
    {
        $qb = $this->createQueryBuilder("u");

        $qb = $qb->andWhere($qb->expr()->in("u.id", ":ids"))
            ->setParameter("ids", $ids);

        $qb = $qb->addOrderBy("u.first_name", "ASC");

        return new Paginator($qb->getQuery());
    }

    /**
     * Находит сущности по заданному расписанию.<br>
     * По умолчанию вернёт первые 100 записей.
     * ___
     * <code>
     *     // Пример:
     *     $result = $userRepository->findBySchedule([
     *          [ "time" => "07:00", "day" => 1 ],
     *          [ "time" => "07:00", "day" => 2 ],
     *          [ "time" => "07:00", "day" => 3 ],
     *          [ "time" => "07:00", "day" => 4 ],
     *          [ "time" => "07:00", "day" => 5 ],
     *          [ "time" => "07:00", "day" => 6 ],
     *          [ "time" => "07:00", "day" => 7 ],
     *      ]);
     * </code>
     *
     * @param array $schedule
     * @param int   $offset
     * @param int   $count
     *
     * @return Paginator
     */
    public function findBySchedule(array $schedule, int $offset = 0, int $count = 100): Paginator
    {
        $qb = $this->createQueryBuilder("u");
        $qb = $qb->andWhere("JSON_OVERLAPS(u.schedule, :schedule) = 1")
            ->setParameter("schedule", json_encode($schedule))
            ->setFirstResult($offset)
            ->setMaxResults($count);

        return new Paginator($qb->getQuery());
    }

    /**
     * @param \Doctrine\Common\Collections\Criteria $criteria
     *
     * @return iterable<\App\Entity\User>
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function toIterable(Criteria $criteria): iterable
    {
        return $this
            ->createQueryBuilder("u")
            ->addCriteria($criteria)
            ->getQuery()
            ->disableResultCache()
            ->toIterable();
    }

    /**
     * Вернёт свободного оператора или NULL еси таковой не найдётся.
     *
     * @param string $notEarlier
     *
     * @return \App\Entity\User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneFree(string $notEarlier = "10 seconds"): ?Entity\User
    {
        $qb = $this->createQueryBuilder("u");

        $qb = $qb->andWhere("u.mode = :mode");
        $qb = $qb->andWhere("u.dialer_state = :dialer_state");
//        $qb = $qb->andWhere("u.last_call_at < :last_call_at");

        $qb = $qb->setParameters([
           "mode" => UserModeType::convertToDBValue(UserModeType::INCOMING_AUTODIALER),
           "dialer_state" => DialerStateType::convertToDBValue(DialerStateType::NOT_INUSE),
//           "last_call_at" => (new DateTime())->sub(\DateInterval::createFromDateString($notEarlier)),
        ]);

        $qb = $qb->orderBy("u.last_call_at", "DESC");
        $qb = $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
