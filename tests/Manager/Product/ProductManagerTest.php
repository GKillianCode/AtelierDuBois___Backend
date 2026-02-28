<?php

namespace App\Tests\Manager\Product;

use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Manager\Product\ProductManager;
use App\Manager\Product\ProductReviewManager;
use App\Mapper\Product\ProductVariantMapper;
use App\Repository\Product\ProductVariantRepository;
use App\Util\ValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;

class ProductManagerTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private EntityManagerInterface&MockObject $entityManager;
    private ProductVariantRepository&MockObject $productVariantRepository;
    private ProductVariantMapper&MockObject $productVariantMapper;
    private ProductReviewManager&MockObject $productReviewManager;
    private ProductManager $sut;

    protected function setUp(): void
    {
        $this->logger                   = $this->createMock(LoggerInterface::class);
        $this->entityManager            = $this->createMock(EntityManagerInterface::class);
        $this->productVariantRepository = $this->createMock(ProductVariantRepository::class);
        $this->productVariantMapper     = $this->createMock(ProductVariantMapper::class);
        $this->productReviewManager     = $this->createMock(ProductReviewManager::class);

        // Real ValidatorUtil — none of the tested paths call validateAndSave,
        // so no DB interaction is triggered.
        $validatorUtil = new ValidatorUtil(
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
            $this->createMock(LoggerInterface::class)
        );

        $this->sut = new ProductManager(
            $this->logger,
            $this->entityManager,
            $validatorUtil,
            $this->productVariantRepository,
            $this->productVariantMapper,
            $this->productReviewManager
        );
    }

    private function buildProduct(): Product
    {
        return new Product();
    }

    // --- update ---

    public function testUpdateSetsUpdatedAtAndFlushes(): void
    {
        $product = $this->buildProduct();
        $before  = new \DateTimeImmutable('2000-01-01');
        $product->setUpdatedAt($before);

        $this->entityManager->expects($this->once())->method('flush');
        $this->logger->expects($this->never())->method('error');

        $this->sut->update($product);

        $this->assertGreaterThan($before, $product->getUpdatedAt());
    }

    public function testUpdateLogsErrorWhenFlushThrows(): void
    {
        $product = $this->buildProduct();
        $this->entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error updating product', $this->arrayHasKey('exception'));

        $this->sut->update($product);
    }

    // --- delete ---

    public function testDeleteCallsRemoveThenFlush(): void
    {
        $product = $this->buildProduct();

        $this->entityManager->expects($this->once())->method('remove')->with($product);
        $this->entityManager->expects($this->once())->method('flush');
        $this->logger->expects($this->never())->method('error');

        $this->sut->delete($product);
    }

    public function testDeleteLogsErrorWhenFlushThrows(): void
    {
        $product = $this->buildProduct();
        $this->entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error deleting product', $this->arrayHasKey('exception'));

        $this->sut->delete($product);
    }

    // --- getProductVariantByPublicId ---

    public function testGetProductVariantByPublicIdDelegatesToRepository(): void
    {
        $variant = new ProductVariant();
        $this->productVariantRepository->expects($this->once())
            ->method('getProductVariantByPublicId')
            ->with('abc123publicid0000000')
            ->willReturn($variant);

        $result = $this->sut->getProductVariantByPublicId('abc123publicid0000000');

        $this->assertSame($variant, $result);
    }

    public function testGetProductVariantByPublicIdReturnsNullWhenNotFound(): void
    {
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn(null);

        $this->assertNull($this->sut->getProductVariantByPublicId('doesNotExist000000000'));
    }
}
