<?php

namespace App\Dto\Types;

use App\Dto\Types\PublicIdDto;

class CategoryDto
{
    public function __construct(
        public readonly string $name,
        public readonly PublicIdDto $publicId,
    ) {}
}
