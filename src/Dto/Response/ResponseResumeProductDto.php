<?php

namespace App\Dto\Response;

use App\Enum\ProductType;
use App\Dto\Types\ImageDto;
use App\Dto\Types\PriceDto;
use App\Dto\Types\CategoryDto;
use App\Dto\Types\PublicIdDto;

class ResponseResumeProductDto
{
    public function __construct(
        private readonly int $id,
        private readonly string $title,
        private readonly ProductType $type,
        private readonly CategoryDto $category,
        private readonly ?PriceDto $unitPrice,
        private readonly ImageDto $mainImage,
        private readonly PublicIdDto $publicId,
        private ?int $averageRating = null
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): ProductType
    {
        return $this->type;
    }

    public function getCategory(): CategoryDto
    {
        return $this->category;
    }

    public function getUnitPrice(): ?PriceDto
    {
        return $this->unitPrice;
    }

    public function getMainImage(): ImageDto
    {
        return $this->mainImage;
    }

    public function getPublicId(): PublicIdDto
    {
        return $this->publicId;
    }

    public function getAverageRating(): ?int
    {
        return $this->averageRating;
    }

    public function setAverageRating(?int $averageRating): void
    {
        $this->averageRating = $averageRating;
    }
}
