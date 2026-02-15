<?php

namespace App\Manager\Product;

use App\Util\ValidatorUtil;
use Psr\Log\LoggerInterface;
use App\Entity\Product\Product;
use App\Trait\ValidateAndSaveTrait;
use App\Entity\Product\ProductVariant;
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
        private readonly ProductVariantMapper $productVariantMapper,
        private readonly ProductReviewManager $productReviewManager,
    ) {}

    /**
     * Updates an existing product
     *
     * @param Product $product The product entity to update
     * @return void
     */
    public function update(Product $product): void
    {
        $product->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    /**
     * Deletes a product
     *
     * @param Product $product The product entity to delete
     * @return void
     */
    public function delete(Product $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    use ValidateAndSaveTrait;

    /**
     * Retrieves a product variant by its public ID
     *
     * @param string $publicId The public ID of the product variant
     * @return ProductVariant|null The product variant or null if not found
     */
    public function getProductVariantByPublicId(string $publicId)
    {
        $productVariant = $this->productVariantRepository->getProductVariantByPublicId($publicId);
        return $productVariant;
    }
}
