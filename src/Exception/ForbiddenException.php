<?php

namespace App\Exception;

class ForbiddenException extends AppException
{
    public function __construct(string $message = 'Access denied')
    {
        parent::__construct(
            message: $message,
            errorCode: 'forbidden',
            httpStatusCode: 403,
        );
    }
}
