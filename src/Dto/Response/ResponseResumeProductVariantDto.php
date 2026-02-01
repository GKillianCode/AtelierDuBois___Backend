<?php

namespace App\Dto\Response;

use App\Dto\Types\PublicIdDto;

class ResponseResumeProductVariantDto
{
    public function __construct(
        public PublicIdDto $publicId,
        public int $unitPrice,
        public string $wood,
        public string $imageUrl,
    ) {}
}
