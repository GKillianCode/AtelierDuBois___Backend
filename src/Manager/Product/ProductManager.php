<?php

namespace App\Manager\Product;

use App\Util\ValidatorUtil;
use Psr\Log\LoggerInterface;
use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Trait\ValidateAndSaveTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Mapper\Product\ProductVariantMapper;
use App\Repository\Product\ProductVariantRepository;

class ProductManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorUtil $validatorUtil,
        private readonly ProductVariantRepository $productVariantRepository,
        /** @phpstan-ignore property.onlyWritten */
        private readonly ProductVariantMapper $productVariantMapper,
        /** @phpstan-ignore property.onlyWritten */
        private readonly ProductReviewManager $productReviewManager,
    ) {}

    public function update(Product $product): void
    {
        try {
            $product->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error updating product',
                [
                    'exception' => $e->getMessage(),
                    'productId' => $product->getId()
                ]
            );
        }
    }

    public function delete(Product $product): void
    {
        try {
            $this->entityManager->remove($product);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error deleting product',
                [
                    'exception' => $e->getMessage(),
                    'productId' => $product->getId()
                ]
            );
        }
    }

    use ValidateAndSaveTrait;

    public function getProductVariantByPublicId(string $publicId): ?ProductVariant
    {
        $productVariant = $this->productVariantRepository->getProductVariantByPublicId($publicId);
        return $productVariant;
    }
}
