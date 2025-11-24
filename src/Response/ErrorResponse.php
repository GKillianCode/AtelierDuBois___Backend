<?php

namespace App\Response;

class ErrorResponse
{
    public function __construct(
        private readonly string $code,
        private readonly string $message,
        private readonly ?array $details = null,
        private readonly ?string $userMessage = null
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
                'details' => $this->details,
                'userMessage' => $this->userMessage,
            ]
        ]);
    }
}
