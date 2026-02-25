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
use App\Exception\NotFoundException;

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
        try {
            $paginator = $this->productRepository->paginateProducts($getAllProductsRequestDto);

            $productsDto = $this->productVariantMapper->mapProductsToShortDtos($paginator);
            $ratings = $this->productRepository->getAverageRatingsForProducts($productsDto);

            foreach ($productsDto as $productDto) {
                $rating = $ratings[$productDto->getId()] ?? null;
                $productDto->setAverageRating(isset($rating) ? (int) round($rating) : null);
            }

            $paginationDataDto = $this->paginationUtil->getMetaPaginationData($paginator, $getAllProductsRequestDto->getLimit(), $getAllProductsRequestDto->getPage());

            return [
                'products' => $productsDto,
                'pagination' => $paginationDataDto
            ];
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error retrieving paginated products',
                [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
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
        try {
            $productVariant = $this->productManager->getProductVariantByPublicId($getProductReviewsRequestDto->getProductVariantPublicId());

            if (!$productVariant) {
                throw new NotFoundException('ProductVariant', $getProductReviewsRequestDto->getProductVariantPublicId());
            }

            $result = $this->productReviewManager->getReviewsByVariantId($getProductReviewsRequestDto);

            return $result;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error retrieving product variant reviews',
                [
                    'exception' => $e->getMessage(),
                    'publicId' => $getProductReviewsRequestDto->getProductVariantPublicId(),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

    /**
     * Finds a product variant with all its associated details
     *
     * @param string $publicId The public ID of the product variant
     * @return ResponseProductDto|null The detailed product DTO or null if not found
     */
    public function findVariantWithDetails(string $publicId): ResponseProductDto|null
    {
        try {
            $productVariant = $this->productManager->getProductVariantByPublicId($publicId);

            if (!$productVariant) {
                throw new NotFoundException('ProductVariant', $publicId);
            }

            $productId = $productVariant->getProductId()->getId();
            $productsVariants = $this->productVariantRepository->getAllMinimalProductVariant($productId);

            if (!$productsVariants) {
                throw new NotFoundException('ProductVariant', $publicId);
            }

            $otherProductsVariantsDto = $this->productVariantMapper->mapVariantsToOtherVariantDtos($productsVariants);
            $responseProductDto = $this->productVariantMapper->variantToDto($productVariant, $otherProductsVariantsDto);

            return $responseProductDto;
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error finding variant with details',
                [
                    'exception' => $e->getMessage(),
                    'publicId' => $publicId,
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }
}
