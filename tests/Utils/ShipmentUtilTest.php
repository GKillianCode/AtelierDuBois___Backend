<?php

namespace App\Tests\Utils;

use App\Util\ShipmentUtil;
use App\Util\UuidUtil;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ShipmentUtilTest extends TestCase
{
    private ShipmentUtil $sut;

    protected function setUp(): void
    {
        // UuidUtil is pure — real implementation
        $uuidUtil  = new UuidUtil($this->createMock(LoggerInterface::class));
        $this->sut = new ShipmentUtil($uuidUtil);
    }

    // =========================================================================
    // calculateNumberOfShipments
    // =========================================================================

    public function testCalculateNumberOfShipmentsExactDivision(): void
    {
        $this->assertSame(2, $this->sut->calculateNumberOfShipments(10, 5));
    }

    public function testCalculateNumberOfShipmentsCeilsPartialBox(): void
    {
        $this->assertSame(3, $this->sut->calculateNumberOfShipments(11, 5));
    }

    public function testCalculateNumberOfShipmentsSingleItemFitsInBox(): void
    {
        $this->assertSame(1, $this->sut->calculateNumberOfShipments(1, 5));
    }

    public function testCalculateNumberOfShipmentsQuantityEqualsMaxStackSize(): void
    {
        $this->assertSame(1, $this->sut->calculateNumberOfShipments(5, 5));
    }

    public function testCalculateNumberOfShipmentsOneItemPerBox(): void
    {
        $this->assertSame(4, $this->sut->calculateNumberOfShipments(4, 1));
    }

    // =========================================================================
    // generateNewOrderNumber
    // =========================================================================

    public function testGenerateNewOrderNumberMatchesFormat(): void
    {
        $result = $this->sut->generateNewOrderNumber();

        // ORD-{YY}{MM}-{8 uppercase alphanumeric chars}
        $this->assertMatchesRegularExpression('/^ORD-\d{4}-[0-9A-Z]{8}$/', $result);
    }

    public function testGenerateNewOrderNumberContainsCurrentYearMonth(): void
    {
        $yearMonth = (new \DateTimeImmutable())->format('ym');
        $result    = $this->sut->generateNewOrderNumber();

        $this->assertStringStartsWith("ORD-{$yearMonth}-", $result);
    }

    public function testGenerateNewOrderNumberIsUnique(): void
    {
        $this->assertNotSame(
            $this->sut->generateNewOrderNumber(),
            $this->sut->generateNewOrderNumber(),
        );
    }
}
