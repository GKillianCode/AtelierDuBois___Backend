<?php

namespace App\Dto\Order;

class ShortOrderDto
{
    public function __construct(
        private readonly string $orderNumber,
        private readonly string $trackingNumber,
        private readonly int $productCount,
        private readonly int $totalAmount,
        private readonly string $status,
        private readonly \DateTimeInterface $createdAt,
    ) {}

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    public function getProductCount(): int
    {
        return $this->productCount;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
