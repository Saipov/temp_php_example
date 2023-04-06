<?php


namespace App\Classes;


use DateTime;

/**
 * Class DateRange
 *
 * @package App\Classes
 */
class DateRange
{
    private DateTime $start;
    private DateTime $end;

    /**
     * DateRange constructor.
     *
     * @param DateTime|null $start
     * @param DateTime|null $end
     */
    public function __construct(DateTime $start = null, DateTime $end = null)
    {
        $this->start = $start ?? new DateTime();
        $this->end = $end ?? new DateTime();
    }

    /**
     * @return DateTime
     */
    public function getStart(): DateTime
    {
        return $this->start;
    }

    /**
     * @param DateTime $start
     */
    public function setStart(DateTime $start): void
    {
        $this->start = $start;
    }

    /**
     * @return DateTime
     */
    public function getEnd(): DateTime
    {
        return $this->end;
    }

    /**
     * @param DateTime $end
     */
    public function setEnd(DateTime $end): void
    {
        $this->end = $end;
    }
}