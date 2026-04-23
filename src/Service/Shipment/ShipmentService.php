<?php

namespace App\Service\Shipment;

use App\Dto\Order\OrderItemDto;
use App\Dto\Request\Filter\GetShipmentHistoryRequestDto;
use App\Dto\Response\ResponseOrderItemDto;
use App\Dto\Response\ResponseShipmentsHistoryDto;
use App\Dto\Response\ResponseShipmentsPreviewDto;
use App\Entity\User\User;
use App\Manager\Shipment\ShipmentManager;

class ShipmentService
{
    public function __construct(
        private readonly ShipmentManager $shipmentManager,
    ) {}

    /**
     * @param OrderItemDto[] $orderItems
     * @return ResponseOrderItemDto[]
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

    /**
     * @param OrderItemDto[] $orderItems
     */
    public function purchaseOrder(array $orderItems, User $user): void
    {
        $this->shipmentManager->buildShipmentPurchase($orderItems, $user);
    }

    /** @return ResponseShipmentsHistoryDto[] */
    public function getShipmentHistory(User $user, GetShipmentHistoryRequestDto $getShipmentHistoryRequestDto): array
    {
        return $this->shipmentManager->buildShipmentHistory($user, $getShipmentHistoryRequestDto);
    }
}
