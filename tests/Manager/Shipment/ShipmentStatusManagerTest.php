<?php

namespace App\Tests\Manager\Shipment;

use App\Entity\Shipment\OrderStatus;
use App\Exception\NotFoundException;
use App\Manager\Shipment\ShipmentStatusManager;
use App\Repository\Shipment\OrderStatusRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShipmentStatusManagerTest extends TestCase
{
    private OrderStatusRepository&MockObject $orderStatusRepository;
    private ShipmentStatusManager $sut;

    protected function setUp(): void
    {
        $this->orderStatusRepository = $this->createMock(OrderStatusRepository::class);
        $this->sut                   = new ShipmentStatusManager($this->orderStatusRepository);
    }

    // =========================================================================
    // getShipmentStatusByCode
    // =========================================================================

    public function testGetShipmentStatusByCodeReturnsMatchingStatus(): void
    {
        $status = new OrderStatus();
        $this->orderStatusRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'PENDING'])
            ->willReturn($status);

        $result = $this->sut->getShipmentStatusByCode('PENDING');

        $this->assertSame($status, $result);
    }

    public function testGetShipmentStatusByCodeThrowsNotFoundExceptionWhenMissing(): void
    {
        $this->orderStatusRepository->method('findOneBy')->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->sut->getShipmentStatusByCode('UNKNOWN');
    }
}
