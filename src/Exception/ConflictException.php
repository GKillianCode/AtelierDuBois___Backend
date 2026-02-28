<?php

namespace App\Exception;

class ConflictException extends AppException
{
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
