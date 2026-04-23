<?php

namespace App\Tests\Utils;

use App\Util\UuidUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class UuidUtilTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private UuidUtil $sut;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut    = new UuidUtil($this->logger);
    }

    // =========================================================================
    // generateUuid()
    // =========================================================================

    public function testGenerateUuidReturnsValidUuidV4Format(): void
    {
        $uuid = $this->sut->generateUuid();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            (string) $uuid
        );
    }

    public function testGenerateUuidReturnsDifferentValuesEachCall(): void
    {
        $this->assertNotSame($this->sut->generateUuid(), $this->sut->generateUuid());
    }

    // =========================================================================
    // generateUuid62()
    // =========================================================================

    public function testGenerateUuid62Returns22Characters(): void
    {
        $result = $this->sut->generateUuid62();

        $this->assertSame(22, strlen($result));
    }

    public function testGenerateUuid62ContainsOnlyBase62Chars(): void
    {
        $result = $this->sut->generateUuid62();

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{22}$/', $result);
    }

    public function testGenerateUuid62ReturnsDifferentValuesEachCall(): void
    {
        $this->assertNotSame($this->sut->generateUuid62(), $this->sut->generateUuid62());
    }

    // =========================================================================
    // uuidToBase62()
    // =========================================================================

    public function testUuidToBase62Returns22Characters(): void
    {
        $result = $this->sut->uuidToBase62(Uuid::uuid4());

        $this->assertSame(22, strlen($result));
    }

    public function testUuidToBase62ContainsOnlyBase62Chars(): void
    {
        $result = $this->sut->uuidToBase62(Uuid::uuid4());

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{22}$/', $result);
    }

    public function testUuidToBase62IsDeterministic(): void
    {
        $uuid = Uuid::uuid4();

        $this->assertSame(
            $this->sut->uuidToBase62($uuid),
            $this->sut->uuidToBase62($uuid)
        );
    }

    public function testUuidToBase62ProducesDifferentOutputsForDifferentInputs(): void
    {
        $this->assertNotSame(
            $this->sut->uuidToBase62(Uuid::uuid4()),
            $this->sut->uuidToBase62(Uuid::uuid4())
        );
    }

    public function testUuidToBase62PadsToFullLength(): void
    {
        // The all-zeros UUID should produce a fully padded "0000...0" string
        $uuid = Uuid::fromString('00000000-0000-0000-0000-000000000000');

        $result = $this->sut->uuidToBase62($uuid);

        $this->assertSame(22, strlen($result));
        $this->assertSame('0000000000000000000000', $result);
    }
}
