<?php

namespace App\Factory;

use App\Enum\CarrierCode;
use App\Exception\ForbiddenException;

class CarrierFactory
{
    public function __construct(
        private iterable $handle
    ) {}

    public function getHandler(CarrierCode $carrierCode): ?object
    {
        foreach ($this->handle as $handler) {
            if ($handler::getCarrierCode() === $carrierCode->value) {
                return $handler;
            }
        }

        throw new ForbiddenException("No handler found for carrier code: " . $carrierCode);
    }
}
