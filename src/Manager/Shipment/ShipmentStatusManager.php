<?php

namespace App\Manager\Shipment;

use App\Entity\Shipment\OrderStatus;
use App\Exception\NotFoundException;
use App\Repository\Shipment\OrderStatusRepository;

class ShipmentStatusManager
{
    public function __construct(
        private OrderStatusRepository $orderStatusRepository,
    ) {}

    public function getShipmentStatusByCode(string $code): OrderStatus
    {
        $status = $this->orderStatusRepository->findOneBy(['code' => $code]);

        if (!$status) {
            throw new NotFoundException("OrderStatus", $code);
        }

        return $status;
    }
}
