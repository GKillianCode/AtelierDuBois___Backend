<?php

namespace App\Dto\Product;

class CategoryDto
{
    public function __construct(
        public string $name,
        public PublicIdDto $publicId,
    ) {}
}
