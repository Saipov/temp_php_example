<?php

namespace App\Classes;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM as ORM;

/**
 * NativePaginator
 */
class NativePaginator
{
    /**
     * @var ORM\NativeQuery
     */
    protected $query;
    protected $count;
    private ?Cache $cache;

    /**
     * @param ORM\NativeQuery $query
     */
    public function __construct($query)
    {
        $this->query = $query;
        $this->cache = $query->getEntityManager()
            ->getConfiguration()
            ->getResultCacheImpl();
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @param bool $useCache
     * @param int  $cacheLifeTime
     *
     * @return integer
     * @throws \Doctrine\DBAL\Exception
     */
    public function count(bool $useCache = false, int $cacheLifeTime = 60): int
    {
        $sql = $this->query->getSql();
        $sql = preg_replace("/(limit\s+(\d+|:\w+)|offset\s+(\d+|:\w+)).*?$/is", "", $sql);

        $db = $this->query->getEntityManager()->getConnection();
        $params = [];
        $types = [];
        foreach ($this->query->getParameters() as $parameter) {
            $params[$parameter->getName()] = $parameter->getValue();
            $types[$parameter->getName()] = $parameter->getType();
        }

        if ($useCache) {
            $cache_key = "count_result_" . $this->generateCacheKeys($sql, $params, $types);
            $this->count = $this->cache->fetch($cache_key) ?? 0;
        }

        if(!$this->count) {
            // Определить наличие признака группирования.
            if (preg_match("/FROM.*?GROUP\s+BY/is", $sql)) {
                $sql = "SELECT COUNT(*) FROM ($sql) t";
            } else {
                $sql = explode(' FROM ', $sql);
                $sql[0] = 'SELECT COUNT(*)';
                $sql = implode(' FROM ', $sql);
            }

            $this->count = (int) $db->fetchOne($sql, $params);

            if ($useCache) {
                $this->cache->save($cache_key, $this->count, $cacheLifeTime);
            }
        }

        return $this->count;
    }

    /**
     *
     * @return \Doctrine\ORM\NativeQuery
     */
    public function getQuery(): ORM\NativeQuery
    {
        return $this->query;
    }

    /**
     * @param string $sql
     * @param array  $params
     * @param array  $types
     * @param array  $connectionParams
     *
     * @return string
     */
    public function generateCacheKeys(string $sql, array $params, array $types, array $connectionParams = []): string
    {
        $realCacheKey = 'query=' . $sql .
            '&params=' . serialize($params) .
            '&types=' . serialize($types) .
            '&connectionParams=' . hash('sha256', serialize($connectionParams));

        return sha1($realCacheKey);
    }
}