<?php

namespace App\Dto\Response;

use App\Dto\Types\PriceDto;

class ResponseShipmentsPreviewDto
{
    public function __construct(
        private array $shipments,
        private PriceDto $totalPriceInCents
    ) {}

    public function getShipments()
    {
        return $this->shipments;
    }

    public function getTotalPriceInCents()
    {
        return $this->totalPriceInCents;
    }
}
