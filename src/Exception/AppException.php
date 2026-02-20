<?php

namespace App\Exception;

abstract class AppException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $errorCode,
        private readonly int $httpStatusCode,
        private readonly array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
    public function getContext(): array
    {
        return $this->context;
    }
}
