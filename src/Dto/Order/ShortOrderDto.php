<?php

namespace App\Dto\Order;

class ShortOrderDto
{
    public function __construct(
        public readonly string $orderNumber,
        public readonly ?string $trackingNumber = null,
        public readonly int $productCount = 0,
        public readonly int $totalAmount = 0,
        public readonly string $status,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}
