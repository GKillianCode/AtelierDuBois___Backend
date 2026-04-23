<?php

namespace App\Dto\Response;

use App\Dto\Types\ImageDto;

class ResponseShipmentItemDto
{
    public function __construct(
        private string $publicId,
        private string $name,
        private int $quantity,
        private ImageDto $mainImage,
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

    public function getMainImage(): ImageDto
    {
        return $this->mainImage;
    }
}
