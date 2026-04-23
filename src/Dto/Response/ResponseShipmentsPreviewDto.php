<?php

namespace App\Dto\Response;

use App\Dto\Types\PriceDto;

class ResponseShipmentsPreviewDto
{
    public function __construct(
        /** @var ResponseShipmentItemDto[] */
        private array $shipments,
        private PriceDto $totalPriceInCents
    ) {}

    /** @return ResponseShipmentItemDto[] */
    public function getShipments(): array
    {
        return $this->shipments;
    }

    public function getTotalPriceInCents(): PriceDto
    {
        return $this->totalPriceInCents;
    }
}
