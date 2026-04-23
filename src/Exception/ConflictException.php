<?php

namespace App\Exception;

class ConflictException extends AppException
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(string $message, array $context = [])
    {
        parent::__construct(
            message: $message,
            errorCode: 'conflict',
            httpStatusCode: 409,
            context: $context,
        );
    }
}
