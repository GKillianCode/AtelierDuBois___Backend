<?php

namespace App\Util;

class ShipmentUtil
{
    public function __construct(
        private readonly UuidUtil $uuidUtil,
    ) {}

    public function calculateNumberOfShipments(int $quantity, int $maxStackSize): int
    {
        return (int) ceil($quantity / $maxStackSize);
    }

    public function generateNewOrderNumber(): string
    {
        $yearMonth = (new \DateTimeImmutable())->format('ym');
        $uuid = $this->uuidUtil->generateUuid62();
        $last8Digits = strtoupper(str_split($uuid, 8)[1]);

        return "ORD-{$yearMonth}-{$last8Digits}";
    }
}
