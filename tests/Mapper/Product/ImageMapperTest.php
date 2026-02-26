<?php

namespace App\Tests\Mapper\Product;

use App\Dto\Types\ImageDto;
use App\Entity\Product\Image;
use App\Mapper\Product\ImageMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ImageMapperTest extends TestCase
{
    private ImageMapper $mapper;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mapper = new ImageMapper($this->logger);
    }

    public function testToDtoFromEntity(): void
    {
        $image = new Image();
        $image->setFolderName('products')
            ->setImageName('table-chene')
            ->setFormat('webp')
            ->setIsDefault(true);

        $dto = $this->mapper->toDtoFromEntity($image);

        $this->assertInstanceOf(ImageDto::class, $dto);
        $this->assertSame('products/table-chene.webp', $dto->getImageUrl());
    }

    public function testToDtoFromEntityBuildsUrlCorrectly(): void
    {
        $image = new Image();
        $image->setFolderName('chaises')
            ->setImageName('chaise-noyer-01')
            ->setFormat('webp')
            ->setIsDefault(false);

        $dto = $this->mapper->toDtoFromEntity($image);

        $this->assertSame('chaises/chaise-noyer-01.webp', $dto->getImageUrl());
    }

    public function testToDtoFromEntityConcatenationFormat(): void
    {
        $image = new Image();
        $image->setFolderName('armoires')
            ->setImageName('armoire-cerisier')
            ->setFormat('png')
            ->setIsDefault(false);

        $dto = $this->mapper->toDtoFromEntity($image);

        // Le format est : folderName/imageName.format
        $this->assertStringContainsString('armoires/', $dto->getImageUrl());
        $this->assertStringContainsString('/armoire-cerisier.', $dto->getImageUrl());
        $this->assertStringEndsWith('.png', $dto->getImageUrl());
    }
}
