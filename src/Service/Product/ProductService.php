<?php

namespace App\Service\Product;

use Exception;
use App\Util\PaginationUtil;
use Psr\Log\LoggerInterface;
use App\Manager\Product\ProductManager;
use App\Dto\Response\ResponseProductDto;
use App\Mapper\Product\ProductVariantMapper;
use App\Manager\Product\ProductReviewManager;
use App\Repository\Product\ProductRepository;
use App\Dto\Request\Filter\GetAllProductsRequestDto;
use App\Repository\Product\ProductVariantRepository;
use App\Dto\Request\Filter\GetProductReviewsRequestDto;
use App\Dto\Product\RequestFilter\RequestRatingFiltersDto;

class ProductService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProductRepository $productRepository,
        private readonly ProductVariantRepository $productVariantRepository,
        private readonly PaginationUtil $paginationUtil,
        private readonly ProductVariantMapper $productVariantMapper,
        private readonly ProductManager $productManager,
        private readonly ProductReviewManager $productReviewManager,
    ) {}

    /**
     * Retrieves paginated products according to provided filters
     *
     * @param GetAllProductsRequestDto $getAllProductsRequestDto The DTO containing pagination and filter parameters
     * @return array Array containing products and pagination data
     */
    public function getPaginatedProducts(GetAllProductsRequestDto $getAllProductsRequestDto): array
    {
        $this->logger->debug("ProductService::getPaginatedProducts ENTER");

        $paginator = $this->productRepository->paginateProducts($getAllProductsRequestDto);

        $productsDto = $this->productVariantMapper->mapProductsToShortDtos($paginator);
        $ratings = $this->productRepository->getAverageRatingsForProducts($productsDto);

        foreach ($productsDto as $productDto) {
            $rating = $ratings[$productDto->getId()] ?? null;
            $productDto->setAverageRating(isset($rating) ? (int) round($rating) : null);
        }

        $paginationDataDto = $this->paginationUtil->getMetaPaginationData($paginator, $getAllProductsRequestDto->getLimit(), $getAllProductsRequestDto->getPage());

        $this->logger->debug("ProductService::getPaginatedProducts EXIT");

        return [
            'products' => $productsDto,
            'pagination' => $paginationDataDto
        ];
    }

    /**
     * Retrieves product reviews for a variant by its public ID
     *
     * @param string $publicId The public ID of the product variant
     * @param int $page The page number
     * @param int $limit The items per page limit
     * @param RequestRatingFiltersDto $requestRatingFiltersDto The rating filters
     * @return array Array containing reviews and pagination data
     */
    public function getProductVariantReviews(GetProductReviewsRequestDto $getProductReviewsRequestDto): array
    {
        $this->logger->debug("ProductService::getProductVariantReviews ENTER", ['publicId' => $getProductReviewsRequestDto->getProductVariantPublicId(), 'page' => $getProductReviewsRequestDto->getPage(), 'limit' => $getProductReviewsRequestDto->getLimit()]);

        $productVariant = $this->productManager->getProductVariantByPublicId($getProductReviewsRequestDto->getProductVariantPublicId());
        if (!$productVariant) {
            throw new Exception("Product variant not found for public ID: " . $getProductReviewsRequestDto->getProductVariantPublicId());
        }

        $result = $this->productReviewManager->getReviewsByVariantId($getProductReviewsRequestDto);

        $this->logger->debug("ProductService::getProductVariantReviews EXIT");
        return $result;
    }

    /**
     * Finds a product variant with all its associated details
     *
     * @param string $publicId The public ID of the product variant
     * @return ResponseProductDto|null The detailed product DTO or null if not found
     */
    public function findVariantWithDetails(string $publicId): ResponseProductDto|null
    {
        $this->logger->debug("ProductManager::findVariantWithDetails ENTER", ['publicId' => $publicId]);

        $productVariant = $this->productManager->getProductVariantByPublicId($publicId);

        if (!$productVariant) {
            $this->logger->debug("ProductManager::findVariantWithDetails EXIT - Variant not found", ['publicId' => $publicId]);
            return null;
        }

        $productId = $productVariant->getProductId()->getId();
        $productsVariants = $this->productVariantRepository->getAllMinimalProductVariant($productId);

        if ($productVariant && $productsVariants) {
            $this->logger->debug("ProductManager::findVariantWithDetails EXIT - Success", ['publicId' => $publicId]);

            $otherProductsVariantsDto = $this->productVariantMapper->mapVariantsToOtherVariantDtos($productsVariants);
            $responseProductDto = $this->productVariantMapper->variantToDto($productVariant, $otherProductsVariantsDto);
            return $responseProductDto;
        }

        $this->logger->debug("ProductManager::findVariantWithDetails EXIT - No data found", ['publicId' => $publicId]);
        return null;
    }
}
