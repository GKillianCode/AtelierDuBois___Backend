<?php

namespace App\Dto\OpenApiModel;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'ResponseShipmentItemOAModel',
)]
class ResponseShipmentItemOAModel
{
    public function __construct(
        public string $publicId,
        public string $name,
        public int $quantity,
        public string $mainImageUrl,
    ) {}
}
