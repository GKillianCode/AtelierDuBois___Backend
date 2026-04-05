<?php

namespace App\Interface;

use App\Entity\Shipment\Carrier;
use App\Enum\CarrierCode;

interface CarrierInterface
{
    public static function getCarrierCode(): string;
    public function getCarrier(): Carrier;
    public function getTrackingNumber(): string;
    public function createCarrierShipment(CarrierCode $carrierCode): self;
}
