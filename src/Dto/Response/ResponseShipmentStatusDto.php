<?php

namespace App\Dto\Response;

use App\Enum\ShipmentStatusCode;

class ResponseShipmentStatusDto
{
    public function __construct(
        public readonly string $name,
        public readonly ShipmentStatusCode $code,
    ) {}
}
