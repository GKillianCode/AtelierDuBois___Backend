<?php

namespace App\Dto\Response;

use App\Dto\Types\PriceDto;
use App\Dto\Types\PublicIdDto;

class ResponseResumeProductVariantDto
{
    public function __construct(
        private PublicIdDto $publicId,
        private ?PriceDto $unitPrice,
        private string $wood,
        private string $imageUrl,
    ) {}

    public function getPublicId(): PublicIdDto
    {
        return $this->publicId;
    }

    public function getUnitPrice(): ?int
    {
        return $this->unitPrice?->getAmount();
    }

    public function getWood(): string
    {
        return $this->wood;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }
}
