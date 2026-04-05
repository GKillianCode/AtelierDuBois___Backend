<?php

namespace App\Dto\Response;

use App\Dto\Types\PriceDto;

class ResponseShipmentsDto
{
    public function __construct(
        public string $orderNumber,
        public array $shipments,
        public PriceDto $totalPriceInCents
    ) {}

    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    public function getShipments()
    {
        return $this->shipments;
    }

    public function getTotalPriceInCents()
    {
        return $this->totalPriceInCents;
    }
}
