<?php


namespace App\Message;

use App\Entity\User;

/**
 *
 */
class ImportContact
{
    private string $pathFileName;
    private int $userId;


    /**
     * ImportContact constructor.
     *
     * @param string $pathFileName
     * @param User   $user
     */
    public function __construct(string $pathFileName, int $userId)
    {
        $this->pathFileName = $pathFileName;
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getPathFileName(): string
    {
        return $this->pathFileName;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}
