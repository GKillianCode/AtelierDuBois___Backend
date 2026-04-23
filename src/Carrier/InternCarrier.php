<?php

namespace App\Carrier;

use App\Enum\CarrierCode;

class InternCarrier extends AbstractCarrier
{
    public static function getCarrierCode(): string
    {
        return CarrierCode::INTERN->value;
    }

    protected function generateTrackingNumber(): void
    {
        $uuid = $this->uuidUtil->generateUuid62();
        $this->trackingNumber = "ADB-" . strtoupper($uuid);
    }
}
