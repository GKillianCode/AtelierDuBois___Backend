<?php

namespace App\Dto\Response;

use App\Dto\Types\PriceDto;

class ResponseShipmentsHistoryDto
{
    public function __construct(
        private array $shipments,
        private PriceDto $totalPriceInCents,
        private int $orderedAt
    ) {}

    public function getShipments()
    {
        return $this->shipments;
    }

    public function getTotalPriceInCents()
    {
        return $this->totalPriceInCents;
    }

    public function getOrderedAt()
    {
        return $this->orderedAt;
    }
}
