<?php

namespace App\Tests\Mapper\Product;

use App\Dto\Response\ResponseWoodDto;
use App\Entity\Product\Wood;
use App\Mapper\Product\WoodMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class WoodMapperTest extends TestCase
{
    private WoodMapper $mapper;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mapper = new WoodMapper($this->logger);
    }

    public function testToDtoFromEntity(): void
    {
        $wood = new Wood();
        $wood->setName('Chêne');

        $dto = $this->mapper->toDtoFromEntity($wood);

        $this->assertInstanceOf(ResponseWoodDto::class, $dto);
        $this->assertSame('Chêne', $dto->name);
    }

    public function testToDtoFromEntityPreservesName(): void
    {
        $wood = new Wood();
        $wood->setName('Noyer américain');

        $dto = $this->mapper->toDtoFromEntity($wood);

        $this->assertSame('Noyer américain', $dto->name);
    }
}
