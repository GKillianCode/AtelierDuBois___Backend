<?php

namespace App\Service\Shipment;

use App\Dto\Order\OrderItemDto;
use App\Dto\Response\ResponseShipmentsPreviewDto;
use App\Manager\Shipment\ShipmentManager;

class ShipmentService
{
    public function __construct(
        private readonly ShipmentManager $shipmentManager,
    ) {}

    /**
     * @param OrderItemDto[] $orderItems
     */
    public function getBasketPreview(array $orderItems): array
    {
        return $this->shipmentManager->buildBasketItems($orderItems);
    }

    /**
     * @param OrderItemDto[] $orderItems
     */
    public function getShipmentPreview(array $orderItems): ResponseShipmentsPreviewDto
    {
        return $this->shipmentManager->buildShipmentPreview($orderItems);
    }
}
