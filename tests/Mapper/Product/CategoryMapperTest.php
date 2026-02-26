<?php

namespace App\Tests\Mapper\Product;

use App\Dto\Types\CategoryDto;
use App\Dto\Types\PublicIdDto;
use App\Entity\Product\Category;
use App\Mapper\Product\CategoryMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CategoryMapperTest extends TestCase
{
    private CategoryMapper $mapper;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mapper = new CategoryMapper($this->logger);
    }

    public function testToDtoFromEntity(): void
    {
        $category = new Category();
        $category->setName('Mobilier')
            ->setPublicId('aB3dEfGhIjKlMnOpQrStuV');

        $dto = $this->mapper->toDtoFromEntity($category);

        $this->assertInstanceOf(CategoryDto::class, $dto);
        $this->assertSame('Mobilier', $dto->getName());
        $this->assertInstanceOf(PublicIdDto::class, $dto->getPublicId());
        $this->assertSame('aB3dEfGhIjKlMnOpQrStuV', $dto->getPublicId()->getPublicId());
    }

    public function testToDtoFromEntityPreservesName(): void
    {
        $category = new Category();
        $category->setName('Décoration')
            ->setPublicId('Zz9yY8xX7wW6vV5uU4tT3s');

        $dto = $this->mapper->toDtoFromEntity($category);

        $this->assertSame('Décoration', $dto->getName());
        $this->assertSame('Zz9yY8xX7wW6vV5uU4tT3s', $dto->getPublicId()->getPublicId());
    }
}
