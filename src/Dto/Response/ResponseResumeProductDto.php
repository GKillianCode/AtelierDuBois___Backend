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
        public readonly int $id,
        public readonly string $title,
        public readonly ProductType $type,
        public readonly CategoryDto $category,
        public readonly ?PriceDto $unitPrice,
        public readonly ImageDto $mainImage,
        public readonly PublicIdDto $publicId,
        public ?int $averageRating = null
    ) {}
}
