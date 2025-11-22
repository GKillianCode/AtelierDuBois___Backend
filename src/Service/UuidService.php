<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class UuidService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->generateUuid62();
    }

    public function generateUuid62(): string
    {
        $uuid = Uuid::uuid4()->toString();
        $this->logger->error("Generated UUIDv4: " . $uuid);
        return $uuid;
    }
}
