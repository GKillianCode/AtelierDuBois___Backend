<?php

namespace App\Carrier;

use App\Entity\Shipment\Carrier;
use App\Enum\CarrierCode;
use App\Exception\ForbiddenException;
use App\Interface\CarrierInterface;
use App\Repository\Shipment\CarrierRepository;
use App\Util\UuidUtil;

abstract class AbstractCarrier implements CarrierInterface
{
    protected ?Carrier $carrier = null;
    protected string $trackingNumber = '';

    public function __construct(
        protected readonly CarrierRepository $carrierRepository,
        protected readonly UuidUtil $uuidUtil,
    ) {}

    public function getCarrier(): Carrier
    {
        return $this->carrier;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    public function createCarrierShipment(CarrierCode $carrierCode): self
    {
        $this->getCarrierFromDatabase($carrierCode);
        $this->generateTrackingNumber();

        return $this;
    }

    protected function getCarrierFromDatabase(CarrierCode $carrierCode): void
    {
        $carrierData = $this->carrierRepository->findOneBy(['technicalName' => $carrierCode]);

        try {
            $this->carrier ??= $carrierData;
        } catch (\Exception $e) {
            throw new ForbiddenException("Error retrieving carrier from database: " . $e->getMessage());
        }
    }

    protected function generateTrackingNumber(): void {}
}
