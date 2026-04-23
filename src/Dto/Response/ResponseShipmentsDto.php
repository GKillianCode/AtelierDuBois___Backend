<?php

namespace App\Dto\Response;

use App\Dto\Types\PriceDto;

class ResponseShipmentsDto
{
    public function __construct(
        public string $orderNumber,
        /** @var ResponseShipmentItemDto[] */
        public array $shipments,
        public PriceDto $totalPriceInCents
    ) {}

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

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
