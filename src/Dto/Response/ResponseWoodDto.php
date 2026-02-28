<?php

namespace App\Dto\Response;

class ResponseWoodDto
{
    public function __construct(
        public readonly string $name,
    ) {}
}
