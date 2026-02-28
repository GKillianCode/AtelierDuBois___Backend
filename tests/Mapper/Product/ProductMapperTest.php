<?php

namespace App\Tests\Mapper\Product;

use App\Dto\Response\ResponseResumeProductDto;
use App\Dto\Types\PublicIdDto;
use App\Entity\Product\Category;
use App\Entity\Product\Image;
use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Entity\Product\Wood;
use App\Enum\ProductType;
use App\Mapper\Product\CategoryMapper;
use App\Mapper\Product\ImageMapper;
use App\Mapper\Product\ProductMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ProductMapperTest extends TestCase
{
    private ProductMapper $mapper;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mapper = new ProductMapper(
            $this->logger,
            new ImageMapper($this->logger),
            new CategoryMapper($this->logger)
        );
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function setId(object $entity, int $id): void
    {
        $reflection = new \ReflectionProperty($entity, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($entity, $id);
    }

    private function buildEntityGraph(bool $withStock = true, bool $withPrice = true): array
    {
        $category = new Category();
        $category->setName('Mobilier')
            ->setPublicId('aB3dEfGhIjKlMnOpQrStuV');

        $product = new Product();
        $this->setId($product, 42);
        $product->setName('Table en chêne')
            ->setDescription('Magnifique table en chêne massif')
            ->setWeightInGrams(15000)
            ->setLengthInCentimeters('120')
            ->setWidthInCentimeters(60)
            ->setHeightInCentimeters(75)
            ->setMaxStackSize(1)
            ->setCategoryId($category);

        $wood = new Wood();
        $wood->setName('Chêne');

        $image = new Image();
        $image->setFolderName('products')
            ->setImageName('table-chene')
            ->setFormat('webp')
            ->setIsDefault(true);

        $variant = new ProductVariant();
        $variant->setPublicId('Zz9yY8xX7wW6vV5uU4tT3s')
            ->setIsDefault(true)
            ->setProductId($product)
            ->setWoodId($wood);

        if ($withStock) {
            $variant->setStock(5);
        }

        if ($withPrice) {
            $variant->setPrice(29900);
        }

        return [$category, $product, $wood, $image, $variant];
    }

    // -------------------------------------------------------------------------
    // toDtoFromEntity
    // -------------------------------------------------------------------------

    public function testToDtoFromEntityWithStockAndPrice(): void
    {
        [,,, $image, $variant] = $this->buildEntityGraph(withStock: true, withPrice: true);

        $dto = $this->mapper->toDtoFromEntity($variant, $image);

        $this->assertInstanceOf(ResponseResumeProductDto::class, $dto);
        $this->assertSame(42, $dto->getId());
        $this->assertSame('Table en chêne', $dto->getTitle());
        $this->assertSame(ProductType::IN_STOCK, $dto->getType());
        $this->assertSame('Mobilier', $dto->getCategory()->getName());
        $this->assertSame('aB3dEfGhIjKlMnOpQrStuV', $dto->getCategory()->getPublicId()->getPublicId());
        $this->assertNotNull($dto->getUnitPrice());
        $this->assertSame(29900, $dto->getUnitPrice()->getAmount());
        $this->assertSame('products/table-chene.webp', $dto->getMainImage()->getImageUrl());
        $this->assertInstanceOf(PublicIdDto::class, $dto->getPublicId());
        $this->assertSame('Zz9yY8xX7wW6vV5uU4tT3s', $dto->getPublicId()->getPublicId());
        $this->assertNull($dto->getAverageRating());
    }

    public function testToDtoFromEntityWithoutStockIsCustomMade(): void
    {
        [,,, $image, $variant] = $this->buildEntityGraph(withStock: false, withPrice: false);

        $dto = $this->mapper->toDtoFromEntity($variant, $image);

        $this->assertSame(ProductType::CUSTOM_MADE, $dto->getType());
        $this->assertNull($dto->getUnitPrice());
    }

    public function testToDtoFromEntityWithStockButNoPrice(): void
    {
        [,,, $image, $variant] = $this->buildEntityGraph(withStock: true, withPrice: false);

        $dto = $this->mapper->toDtoFromEntity($variant, $image);

        $this->assertSame(ProductType::IN_STOCK, $dto->getType());
        $this->assertNull($dto->getUnitPrice());
    }
}
