<?php

namespace App\Dto\Response;

use App\Dto\Types\PriceDto;

class ResponseShipmentsHistoryDto
{
    public function __construct(
        /** @var ResponseShipmentItemDto[] */
        private array $shipments,
        private PriceDto $totalPriceInCents,
        private int $orderedAt
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

    public function getOrderedAt(): int
    {
        return $this->orderedAt;
    }
}
