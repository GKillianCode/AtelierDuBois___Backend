<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends JsonResponse
{
    private function __construct(
        bool $success,
        mixed $data = null,
        ?string $message = null,
        int $statusCode = Response::HTTP_OK,
        /** @var array<string, string> */
        array $headers = []
    ) {
        $responseData = [
            'success' => $success,
        ];

        if ($message !== null) {
            $responseData['message'] = $message;
        }

        if ($data !== null) {
            $responseData['data'] = $data;
        }

        parent::__construct($responseData, $statusCode, $headers);
    }

    // ============================================
    // SUCCESS RESPONSES
    // ============================================

    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $statusCode = Response::HTTP_OK
    ): self {
        return new self(true, $data, $message, $statusCode);
    }

    public static function created(mixed $data = null, ?string $message = 'Resource created'): self
    {
        return new self(true, $data, $message, Response::HTTP_CREATED);
    }

    // ============================================
    // ERROR RESPONSES
    // ============================================

    public static function error(
        string $message,
        mixed $errors = null,
        int $statusCode = Response::HTTP_BAD_REQUEST
    ): self {
        $data = $errors !== null ? ['errors' => $errors] : null;
        return new self(false, $data, $message, $statusCode);
    }

    public static function notFound(string $message = 'Resource not found', mixed $data = null): self
    {
        return new self(false, $data, $message, Response::HTTP_NOT_FOUND);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self(false, null, $message, Response::HTTP_UNAUTHORIZED);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self(false, null, $message, Response::HTTP_FORBIDDEN);
    }

    /** @param list<array<string, mixed>> $violations */
    public static function validationError(array $violations, string $message = 'Validation failed'): self
    {
        return new self(false, ['violations' => $violations], $message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function conflict(string $message, mixed $data = null): self
    {
        return new self(false, $data, $message, Response::HTTP_CONFLICT);
    }

    public static function serverError(string $message = 'Internal server error'): self
    {
        return new self(false, null, $message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
