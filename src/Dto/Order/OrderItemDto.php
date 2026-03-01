<?php

namespace App\Dto\Order;

readonly class OrderItemDto
{
    public function __construct(
        private string $publicId,
        private int $quantity,
    ) {}

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
