<?php

namespace App\Dto\Types;

class ShipmentStatusDto
{
    public function __construct(
        private readonly string $code,
        private readonly string $name,
    ) {}

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
