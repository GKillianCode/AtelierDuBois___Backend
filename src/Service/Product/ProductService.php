<?php

namespace App\Service\Product;

use App\Util\PaginationUtil;
use Psr\Log\LoggerInterface;
use App\Dto\Types\PaginationDataDto;
use App\Dto\Response\ResponseProductDto;
use App\Dto\Response\ResponseProductReviewDto;
use App\Dto\Response\ResponseResumeProductDto;
use App\Entity\Product\Product;
use App\Manager\Product\ProductManager;
use App\Mapper\Product\ProductVariantMapper;
use App\Manager\Product\ProductReviewManager;
use App\Repository\Product\ProductRepository;
use App\Repository\Product\ProductVariantRepository;
use App\Dto\Request\Filter\GetAllProductsRequestDto;
use App\Dto\Request\Filter\GetProductReviewsRequestDto;
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
     * @return array{products: ResponseResumeProductDto[], pagination: PaginationDataDto}
     */
    public function getPaginatedProducts(GetAllProductsRequestDto $getAllProductsRequestDto): array
    {
        try {
            $paginator = $this->productRepository->paginateProducts($getAllProductsRequestDto);

            /** @var Product[] $products */
            $products = iterator_to_array($paginator, false);
            $productsDto = $this->productVariantMapper->mapProductsToShortDtos($paginator);
            $ratings = $this->productRepository->getAverageRatingsForProducts($products);

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
     * @return array{reviews: ResponseProductReviewDto[], pagination: PaginationDataDto}
     */
    public function getProductVariantReviews(GetProductReviewsRequestDto $getProductReviewsRequestDto): array
    {
        try {
            $productVariant = $this->productManager->getProductVariantByPublicId($getProductReviewsRequestDto->getProductVariantPublicId()->getPublicId());

            if (!$productVariant) {
                throw new NotFoundException('ProductVariant', $getProductReviewsRequestDto->getProductVariantPublicId()->getPublicId());
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
                    'publicId' => $getProductReviewsRequestDto->getProductVariantPublicId()->getPublicId(),
                    'trace' => $e->getTraceAsString()
                ]
            );
            throw $e;
        }
    }

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
