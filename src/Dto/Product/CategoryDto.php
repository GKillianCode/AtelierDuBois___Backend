<?php

namespace App\Dto\Product;

class CategoryDto
{
    public function __construct(
        public readonly string $name,
        public readonly PublicIdDto $publicId,
    ) {}
}
