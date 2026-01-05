<?php

namespace App\Dto\Product\Wood;

class WoodTypesDto
{
    public function __construct(
        /** @var WoodTypeDto[] */
        public readonly array $woodTypes
    ) {}
}
