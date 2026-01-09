<?php

namespace App\Dto\Product;

class OtherProductVariant
{
    public function __construct(
        public string $publicId,
        public int $unitPrice,
        public string $wood,
        public string $imageUrl,
    ) {}
}
