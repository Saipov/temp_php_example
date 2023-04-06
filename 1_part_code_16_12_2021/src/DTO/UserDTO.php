<?php

namespace App\DTO;

use DateTime;

/**
 * DTO
 */
class UserDTO
{
    /**
     * @param int            $id
     * @param string|null    $first_name
     * @param string|null    $last_name
     * @param string|null    $middle_name
     * @param \DateTime|null $last_activity_at
     * @param int|null       $project_id
     * @param string|null    $project_name
     * @param int|null       $group_id
     * @param string|null    $group_name
     * @param string|null    $dialer_state
     * @param bool           $online
     */
    public function __construct(
        private int       $id,
        private ?string   $first_name = null,
        private ?string   $last_name = null,
        private ?string   $middle_name = null,
        private ?DateTime $last_activity_at = null,
        private ?int      $project_id = null,
        private ?string   $project_name = null,
        private ?int      $group_id = null,
        private ?string   $group_name = null,
        private ?string   $dialer_state = null,
        private bool   $online = false
    )
    {}

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getMiddleName(): ?string
    {
        return $this->middle_name;
    }

    /**
     * @return int|null
     */
    public function getProjectId(): ?int
    {
        return $this->project_id;
    }

    /**
     * @return string|null
     */
    public function getProjectName(): ?string
    {
        return $this->project_name;
    }

    /**
     * @return DateTime|null
     */
    public function getLastActivityAt(): ?DateTime
    {
        return $this->last_activity_at;
    }

    /**
     * @return int|null
     */
    public function getGroupId(): ?int
    {
        return $this->group_id;
    }

    /**
     * @return string|null
     */
    public function getGroupName(): ?string
    {
        return $this->group_name;
    }

    /**
     * @return string|null
     */
    public function getDialerState(): ?string
    {
        return $this->dialer_state;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->online;
    }
}