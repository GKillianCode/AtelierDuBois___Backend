<?php

namespace App\Dto\OpenApiModel;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'ProductResumeOAModel',
)]
class ProductResumeOAModel
{
    public function __construct(
        public string $title,
        public string $type,
        public CategoryOAModel $category,
        public int $unitPrice,
        public string $publicId,
        public string $imageUrl,
        public int $averageRating
    ) {}
}
