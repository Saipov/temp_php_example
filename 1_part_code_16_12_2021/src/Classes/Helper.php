<?php


namespace App\Classes;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

/**
 * Class Helper
 *
 * @package App\Classes
 */
class Helper
{
    /**
     * Конвертирование UNIXTIME в объект класса DatePeriod
     *
     * Примеры:
     * <code>
     * // Левая часть дата и время начала
     * // Правая часть дата и время завершения.
     *  $date_period = unixtimeToDatePeriod('1618347600,1618434000')
     *
     * $date_period = unixtimeToDatePeriod('1618434000')
     * </code>
     *
     * @param string $value
     * @param string $interval_spec
     *
     * @return DatePeriod
     * @throws Exception
     */
    static function unixtimeToDatePeriod($value, string $interval_spec = 'P1D'): DatePeriod
    {
        $start = new DateTime();

        if (preg_match('/^(\d+),(\d+)$/s', $value)) {
            $unix_time_range = Functions::explode(",", $value, 2, ["trim", "intval"]);

            if (!((integer)$unix_time_range[0] <= PHP_INT_MAX) && ((integer)$unix_time_range[0] >= ~PHP_INT_MAX)) {
                throw new InvalidArgumentException("The value is not unixtime");
            }

            if (!((integer)$unix_time_range[1] <= PHP_INT_MAX) && ((integer)$unix_time_range[1] >= ~PHP_INT_MAX)) {
                throw new InvalidArgumentException("The value is not unixtime");
            }

            $start->setTimestamp($unix_time_range[0]);

            $end = clone $start;
            $end->setTimestamp($unix_time_range[1]);
        } else {
            if (!((integer)$value <= PHP_INT_MAX) && ((integer)$value >= ~PHP_INT_MAX)) {
                throw new InvalidArgumentException("The value is not unixtime");
            }
            $start->setTimestamp((int)$value);
            $end = clone $start;
        }

        return new DatePeriod($start, new DateInterval($interval_spec), $end);
    }

    /**
     * Период текущего дня
     *
     * @return DatePeriod
     */
    static function periodOfTheCurrentDay(): DatePeriod
    {
        $start = new DateTime();
        $end = clone $start;

        return new DatePeriod(
            $start->setTime(0, 0, 0),
            new DateInterval("P1D"),
            $end->setTime(23, 59, 59)
        );
    }

    /**
     * Возвращаем смещение по таймзоне в невалидном формате
     * Например: 2,3,12
     *
     * @param string $timezone
     *
     * @return integer
     * @throws \Exception
     */
    static function getUtcOffset(string $timezone): int
    {
        if (empty($timezone)) {
            throw new InvalidArgumentException("The value cannot be blank.");
        }

        $targetTimeZone = new DateTimeZone($timezone);
        $dateTime = new DateTime("now", $targetTimeZone);
        return sprintf("%d", $dateTime->format("P"));
    }
}
