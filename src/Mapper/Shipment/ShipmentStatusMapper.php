<?php

namespace App\Mapper\Shipment;

use Psr\Log\LoggerInterface;
use App\Enum\ShipmentStatusCode;
use App\Entity\Shipment\OrderStatus;
use App\Dto\Response\ResponseShipmentStatusDto;

class ShipmentStatusMapper
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function toDtoFromEntity(OrderStatus $shipmentStatus): ResponseShipmentStatusDto
    {
        $this->logger->debug("ShipmentStatusMapper::toDtoFromEntity ENTER");

        $shipmentStatusDto = new ResponseShipmentStatusDto(
            name: $shipmentStatus->getName(),
            code: ShipmentStatusCode::from($shipmentStatus->getCode()->value),
        );

        $this->logger->debug("ShipmentStatusMapper::toDtoFromEntity EXIT");
        return $shipmentStatusDto;
    }
}
