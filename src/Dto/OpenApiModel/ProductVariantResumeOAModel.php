<?php

namespace App\Dto\OpenApiModel;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'ProductVariantResumeOAModel',
)]
class ProductVariantResumeOAModel
{
    public function __construct(
        public string $title,
        public string $publicId,
        public string $imageUrl,
    ) {}
}
