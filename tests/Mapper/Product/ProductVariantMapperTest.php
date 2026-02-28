<?php

namespace App\Tests\Mapper\Product;

use App\Dto\Response\ResponseProductDto;
use App\Dto\Response\ResponseResumeProductDto;
use App\Dto\Response\ResponseResumeProductVariantDto;
use App\Entity\Product\Category;
use App\Entity\Product\Image;
use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Entity\Product\Wood;
use App\Enum\ProductType;
use App\Mapper\Product\CategoryMapper;
use App\Mapper\Product\ImageMapper;
use App\Mapper\Product\ProductMapper;
use App\Mapper\Product\ProductVariantMapper;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ProductVariantMapperTest extends TestCase
{
    private ProductVariantMapper $mapper;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);

        $imageMapper = new ImageMapper($this->logger);
        $categoryMapper = new CategoryMapper($this->logger);
        $productMapper = new ProductMapper($this->logger, $imageMapper, $categoryMapper);

        $this->mapper = new ProductVariantMapper(
            $this->logger,
            $imageMapper,
            $productMapper,
            $categoryMapper
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

    private function buildEntityGraph(bool $withStock = true, bool $withPrice = true, bool $imageIsDefault = true): array
    {
        $category = new Category();
        $category->setName('Mobilier')
            ->setPublicId('aB3dEfGhIjKlMnOpQrStuV');

        $product = new Product();
        $this->setId($product, 10);
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
            ->setIsDefault($imageIsDefault);

        $variant = new ProductVariant();
        $variant->setPublicId('Zz9yY8xX7wW6vV5uU4tT3s')
            ->setIsDefault(true)
            ->setProductId($product)
            ->setWoodId($wood)
            ->addImage($image);

        if ($withStock) {
            $variant->setStock(5);
        }

        if ($withPrice) {
            $variant->setPrice(29900);
        }

        return [$category, $product, $wood, $image, $variant];
    }

    // -------------------------------------------------------------------------
    // toDtoFromProductVariant
    // -------------------------------------------------------------------------

    public function testToDtoFromProductVariantWithStockAndPrice(): void
    {
        [,,, $image, $variant] = $this->buildEntityGraph();

        $dto = $this->mapper->toDtoFromProductVariant($variant, $image);

        $this->assertInstanceOf(ResponseResumeProductDto::class, $dto);
        $this->assertSame(10, $dto->getId());
        $this->assertSame('Table en chêne', $dto->getTitle());
        $this->assertSame(ProductType::IN_STOCK, $dto->getType());
        $this->assertSame('Mobilier', $dto->getCategory()->getName());
        $this->assertNotNull($dto->getUnitPrice());
        $this->assertSame(29900, $dto->getUnitPrice()->getAmount());
        $this->assertSame('products/table-chene.webp', $dto->getMainImage()->getImageUrl());
        $this->assertSame('Zz9yY8xX7wW6vV5uU4tT3s', $dto->getPublicId()->getPublicId());
    }

    public function testToDtoFromProductVariantWithoutStockIsCustomMade(): void
    {
        [,,, $image, $variant] = $this->buildEntityGraph(withStock: false, withPrice: false);

        $dto = $this->mapper->toDtoFromProductVariant($variant, $image);

        $this->assertSame(ProductType::CUSTOM_MADE, $dto->getType());
        $this->assertNull($dto->getUnitPrice());
    }

    // -------------------------------------------------------------------------
    // mapVariantsToOtherVariantDtos
    // -------------------------------------------------------------------------

    public function testMapVariantsToOtherVariantDtos(): void
    {
        [,,,, $variant] = $this->buildEntityGraph(withStock: true, withPrice: true);

        $dtos = $this->mapper->mapVariantsToOtherVariantDtos([$variant]);

        $this->assertCount(1, $dtos);
        $this->assertInstanceOf(ResponseResumeProductVariantDto::class, $dtos[0]);
        $this->assertSame('Zz9yY8xX7wW6vV5uU4tT3s', $dtos[0]->getPublicId()->getPublicId());
        $this->assertSame('Chêne', $dtos[0]->getWood());
        $this->assertSame(29900, $dtos[0]->getUnitPrice());
        $this->assertSame('products/table-chene.webp', $dtos[0]->getImageUrl());
    }

    public function testMapVariantsToOtherVariantDtosReturnsEmptyArrayForEmptyCollection(): void
    {
        $dtos = $this->mapper->mapVariantsToOtherVariantDtos([]);

        $this->assertSame([], $dtos);
    }

    // -------------------------------------------------------------------------
    // variantToDto
    // -------------------------------------------------------------------------

    public function testVariantToDto(): void
    {
        [,,,, $variant] = $this->buildEntityGraph(withStock: true, withPrice: true, imageIsDefault: true);

        $dto = $this->mapper->variantToDto($variant, []);

        $this->assertInstanceOf(ResponseProductDto::class, $dto);
        $this->assertSame('Magnifique table en chêne massif', $dto->getDescription());
        $this->assertSame(5, $dto->getStock());
        $this->assertSame('Chêne', $dto->getWood());
        $this->assertSame(15000, $dto->getWeightInGrams());
        $this->assertSame(60, $dto->getWidthInCentimeters());
        $this->assertSame(75, $dto->getHeightInCentimeters());
        $this->assertCount(1, $dto->getImageUrls());
        $this->assertSame('products/table-chene.webp', $dto->getImageUrls()[0]);
        $this->assertSame([], $dto->getResponseResumeProductVariantDto());
    }

    public function testVariantToDtoWithoutStock(): void
    {
        [,,,, $variant] = $this->buildEntityGraph(withStock: false, withPrice: false, imageIsDefault: true);

        $dto = $this->mapper->variantToDto($variant, []);

        $this->assertNull($dto->getStock());
    }

    // -------------------------------------------------------------------------
    // mapProductsToShortDtos
    // -------------------------------------------------------------------------

    public function testMapProductsToShortDtosSkipsProductWithoutVariant(): void
    {
        $product = new Product();
        $this->setId($product, 99);

        // Paginator mock itérant sur un produit sans variants
        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$product]));

        $result = $this->mapper->mapProductsToShortDtos($paginator);

        $this->assertSame([], $result);
    }

    public function testMapProductsToShortDtosSkipsVariantWithoutImage(): void
    {
        $category = new Category();
        $category->setName('Mobilier')->setPublicId('aB3dEfGhIjKlMnOpQrStuV');

        $product = new Product();
        $this->setId($product, 20);
        $product->setName('Chaise')
            ->setDescription('Chaise en noyer')
            ->setWeightInGrams(5000)
            ->setLengthInCentimeters('50')
            ->setWidthInCentimeters(50)
            ->setHeightInCentimeters(90)
            ->setMaxStackSize(4)
            ->setCategoryId($category);

        $wood = new Wood();
        $wood->setName('Noyer');

        $variantWithoutImage = new ProductVariant();
        $variantWithoutImage->setPublicId('ABCDE12345FGHIJ67890XY')
            ->setIsDefault(true)
            ->setStock(2)
            ->setProductId($product)
            ->setWoodId($wood);

        $product->addProductVariant($variantWithoutImage);

        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$product]));

        $result = $this->mapper->mapProductsToShortDtos($paginator);

        $this->assertSame([], $result);
    }

    public function testMapProductsToShortDtosReturnsDto(): void
    {
        [, $product,,, $variant] = $this->buildEntityGraph();
        $product->addProductVariant($variant);

        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$product]));

        $result = $this->mapper->mapProductsToShortDtos($paginator);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(ResponseResumeProductDto::class, $result[0]);
        $this->assertSame('Table en chêne', $result[0]->getTitle());
    }
}
