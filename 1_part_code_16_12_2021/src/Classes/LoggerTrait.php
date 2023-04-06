<?php

namespace App\Classes;

use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * @required
     *
     * @param LoggerInterface $dbLogger
     */
    public function setLogger(LoggerInterface $dbLogger)
    {
        $this->logger = $dbLogger;
    }

    public function logInfo(string $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }
}
