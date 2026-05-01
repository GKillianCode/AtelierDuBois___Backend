<?php

namespace App\Dto\Response;

use App\Dto\Types\PriceDto;

class ResponseShipmentsHistoryDto
{
    public function __construct(
        /** @var ResponseShipmentItemDto[] */
        private array $shipments,
        private PriceDto $totalPriceInCents,
        private int $orderedAt,
        private string $shipmentId,
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

    public function getShipmentId(): string
    {
        return $this->shipmentId;
    }
}
