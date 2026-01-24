<?php

namespace App\Dto\Response;

use App\Dto\Types\PublicIdDto;

class ResponseAddressDto
{
    public function __construct(
        public PublicIdDto $publicId,
        public readonly string $street,
        public readonly string $city,
        public readonly string $zipcode,
        public readonly bool $isProfessionnal,
        public readonly bool $isDefault
    ) {}
}
