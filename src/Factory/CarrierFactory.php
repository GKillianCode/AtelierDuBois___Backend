<?php

namespace App\Factory;

use App\Enum\CarrierCode;
use App\Exception\ForbiddenException;
use App\Interface\CarrierInterface;

class CarrierFactory
{
    public function __construct(
        /** @var iterable<CarrierInterface> */
        private iterable $handle
    ) {}

    public function getHandler(CarrierCode $carrierCode): ?object
    {
        foreach ($this->handle as $handler) {
            if ($handler::getCarrierCode() === $carrierCode->value) {
                return $handler;
            }
        }

        throw new ForbiddenException("No handler found for carrier code: " . $carrierCode->value);
    }
}
