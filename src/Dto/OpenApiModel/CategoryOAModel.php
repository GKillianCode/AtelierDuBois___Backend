<?php

namespace App\Dto\OpenApiModel;

use OpenApi\Attributes as OA;


#[OA\Schema(
    title: 'CategoryOAModel',
)]
class CategoryOAModel
{
    public function __construct(
        public string $name,
        public string $publicId
    ) {}
}
