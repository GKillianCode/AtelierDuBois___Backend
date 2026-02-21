<?php

namespace App\Dto\OpenApiModel;

use OpenApi\Attributes as OA;


#[OA\Schema(
    title: 'AddressOAModel',
)]
class AddressOAModel
{
    public function __construct(
        public string $street,
        public string $city,
        public string $postalCode,
        public string $country,
        public ?string $publicId
    ) {}
}
