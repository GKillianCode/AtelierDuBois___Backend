<?php

namespace App\Mapper\Shipment;

use Psr\Log\LoggerInterface;
use App\Entity\Shipment\Carrier;
use App\Entity\Shipment\Shipment;
use App\Dto\Response\ResponseCarrierDto;

class CarrierMapper
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function toDtoFromEntity(Carrier $carrier, Shipment $shipment): ResponseCarrierDto
    {
        $this->logger->debug("CarrierMapper::toDtoFromEntity ENTER");

        $responseCarrierDto = ResponseCarrierDto::fromCarrierAndShipment($carrier, $shipment);

        $this->logger->debug("CarrierMapper::toDtoFromEntity EXIT");
        return $responseCarrierDto;
    }
}
