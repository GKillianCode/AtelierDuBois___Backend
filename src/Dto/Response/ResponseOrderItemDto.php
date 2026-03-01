<?php

namespace App\Dto\Response;

class ResponseOrderItemDto
{
    public function __construct(
        private string $publicId,
        private int $quantity,
        private string $name,
        private int $priceInCents,
    ) {}

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriceInCents(): int
    {
        return $this->priceInCents;
    }
}
