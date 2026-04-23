<?php

namespace App\Exception;

class NotFoundException extends AppException
{
    public function __construct(string $resource, int|string $id)
    {
        parent::__construct(
            message: \sprintf('%s with id `%s` is not found.', $resource, $id),
            errorCode: strtolower($resource) . '_not_found',
            httpStatusCode: 404,
            context: ['resource' => $resource, 'id' => $id],
        );
    }
}
