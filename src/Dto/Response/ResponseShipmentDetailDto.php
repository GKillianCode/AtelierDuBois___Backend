<?php

namespace App\Dto\Response;

use App\Dto\Types\ShipmentStatusDto;

class ResponseShipmentDetailDto
{
    /**
     * @param ResponseShipmentItemDto[] $items
     */
    public function __construct(
        private readonly array $items,
        private readonly string $shipmentId,
        private readonly int $orderedAt,
        private readonly ShipmentStatusDto $status,
    ) {}

    /** @return ResponseShipmentItemDto[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getShipmentId(): string
    {
        return $this->shipmentId;
    }

    public function getOrderedAt(): int
    {
        return $this->orderedAt;
    }

    public function getStatus(): ShipmentStatusDto
    {
        return $this->status;
    }
}
