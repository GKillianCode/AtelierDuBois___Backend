<?php

namespace App\Dto\Product;

class WoodTypesDto
{
    public function __construct(
        /** @var WoodTypeDto[] */
        public readonly array $woodTypes
    ) {}
}
