<?php

namespace App\Dto\Response;

use App\Entity\Shipment\Carrier;
use App\Entity\Shipment\Shipment;

class ResponseCarrierDto
{
    private function __construct(
        public readonly string $name,
        public readonly string $trackingUrl,
    ) {}

    public static function fromCarrierAndShipment(Carrier $carrier, Shipment $shipment): self
    {
        $trackingUrl = self::buildTrackingUrl($carrier, $shipment);

        return new self(
            name: $carrier->getName(),
            trackingUrl: $trackingUrl
        );
    }

    private static function buildTrackingUrl(Carrier $carrier, Shipment $shipment): string
    {
        $template = $carrier->getTrackingUrlTemplate();
        $trackingNumber = $shipment->getTrackingNumber();

        if (!$template || !$trackingNumber) {
            return '';
        }

        return str_replace(
            ['{tracking_number}', '{TRACKING_NUMBER}'],
            $trackingNumber,
            $template
        );
    }
}
