<?php

namespace App\Service\Product;

use App\Enum\ProductType;
use Psr\Log\LoggerInterface;
use App\Dto\Product\PriceDto;
use App\Dto\Product\CategoryDto;
use App\Dto\Product\PublicIdDto;
use App\Service\PaginationService;
use App\Enum\CommentSortFilterCode;
use App\Dto\Product\ShortProductDto;
use App\Dto\Product\ProductDetailDto;
use App\Dto\Product\ProductReviewDto;
use App\Entity\Product\ProductReview;
use App\Service\Product\ImageService;
use App\Entity\Product\ProductVariant;
use App\Dto\Product\OtherProductVariant;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Dto\Product\RequestRatingFiltersDto;
use App\Dto\Product\RequestProductFiltersDto;
use App\Repository\Product\ProductRepository;
use App\Repository\Product\ProductReviewRepository;
use App\Repository\Product\ProductVariantRepository;

class ProductService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProductRepository $productRepository,
        private readonly ProductVariantRepository $productVariantRepository,
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly ImageService $imageService,
        private readonly PaginationService $paginationService
    ) {}

    public function getAllProducts(int $page, int $limit, RequestProductFiltersDto $requestFiltersDto): array
    {
        $this->logger->debug("ProductService::getAllProducts ENTER");

        $page = max(1, $page);
        $limit = min(80, max(1, $limit));

        $paginator = $this->productRepository->paginateProducts($page, $limit, $requestFiltersDto);

        $productsDto = $this->getAllProductsInShortProductDto($paginator);
        $ratings = $this->productRepository->getAverageRatingsForProducts($productsDto);

        foreach ($productsDto as $productDto) {
            $productDto->averageRating = isset($ratings[$productDto->id]) ? (int) round($ratings[$productDto->id]) : null;
        }

        $paginationDataDto = $this->paginationService->getMetaPaginationData($paginator, $limit, $page);

        $this->logger->debug("ProductService::getAllProducts EXIT");

        return [
            'products' => $productsDto,
            'pagination' => $paginationDataDto
        ];
    }

    public function getProductReviewsByProductVariantPublicId(string $publicId, int $page, int $limit, RequestRatingFiltersDto $requestRatingFiltersDto): array
    {
        $this->logger->debug("ProductService::getProductReviewsByProductVariantPublicId ENTER", ['publicId' => $publicId, 'page' => $page, 'limit' => $limit]);

        $page = max(1, $page);
        $limit = min(10, max(1, $limit));

        $productVariant = $this->productVariantRepository->findOneBy(['publicId' => $publicId]);


        if (!$productVariant) {
            $this->logger->debug("ProductService::getProductReviewsByProductVariantPublicId EXIT 1 - Product variant not found", ['publicId' => $publicId]);
            return [
                'reviews' => [],
                'pagination' => null
            ];
        }

        $paginator = $this->productReviewRepository->paginateProductReviews($page, $limit, $productVariant->getId(), $requestRatingFiltersDto);

        $reviewsDto = [];

        foreach ($paginator as $review) {
            $user = $review->getUserId();
            if ($user) {
                $author = $user->getFirstName() . ' ' . $user->getLastName();
                $reviewsDto[] = $this->productReviewToProductReviewDto($review, $author);
            }
        }


        $paginationDataDto = $this->paginationService->getMetaPaginationData($paginator, $limit, $page);

        $this->logger->debug("ProductService::getProductReviewsByProductVariantPublicId EXIT");

        return [
            'products' => $reviewsDto,
            'pagination' => $paginationDataDto
        ];
    }

    public function getProductVariantByPublicId(string $publicId)
    {
        $this->logger->debug("ProductService::getProductVariantByPublicId ENTER", ['publicId' => $publicId]);

        $productVariant = $this->productVariantRepository->getProductVariantByPublicId($publicId);
        $productId = $productVariant->getProductId()->getId();
        $productsVariants = $this->productVariantRepository->getAllMinimalProductVariant($productId);

        if ($productVariant && $productsVariants) {
            $this->logger->debug("ProductService::getProductVariantByPublicId EXIT 1", ['publicId' => $publicId]);

            $otherProductsVariantsDto = $this->ProductsVariantsToOtherProductVariantsDto($productsVariants);
            $productDetailDto = $this->ProductVariantToProductDetailDto($productVariant, $otherProductsVariantsDto);
            return $productDetailDto;
        }

        $this->logger->debug("ProductService::getProductVariantByPublicId EXIT 2", ['publicId' => $publicId]);
        return null;
    }

    private function getAllProductsInShortProductDto(Paginator $paginator): array
    {
        $this->logger->debug("ProductService::getAllProductsInShortProductDto ENTER");

        $products = [];
        foreach ($paginator as $product) {
            $defaultVariant = $product->getProductVariants()->first();

            if (!$defaultVariant) {
                $this->logger->warning("Product without default variant", [
                    'productId' => $product->getId()
                ]);
                continue;
            }

            if ($defaultVariant->getImages()->isEmpty()) {
                $this->logger->warning("Product variant without image", [
                    'variantId' => $defaultVariant->getId()
                ]);
                continue;
            }

            $defaultImage = $defaultVariant->getImages()->first();

            $productType = $defaultVariant->getStock() != null ? ProductType::IN_STOCK : ProductType::CUSTOM_MADE;

            $products[] = new ShortProductDto(
                id: $product->getId(),
                title: $product->getName(),
                type: $productType,
                category: new CategoryDto(
                    name: $product->getCategoryId()->getName(),
                    publicId: new PublicIdDto($product->getCategoryId()->getPublicId())
                ),
                unitPrice: new PriceDto($defaultVariant->getPrice()),
                mainImage: $this->imageService->imageToImageDto($defaultImage),
                publicId: new PublicIdDto($defaultVariant->getPublicId())
            );
        }

        $this->logger->debug("ProductService::getAllProductsInShortProductDto EXIT");

        return $products;
    }

    private function ProductsVariantsToOtherProductVariantsDto($productsVariants): array
    {
        $this->logger->debug("ProductService::ProductsVariantsToOtherProductVariantsDto ENTER");
        $otherProductVariants = [];
        foreach ($productsVariants as $productVariant) {
            $imageDto = $this->imageService->imageToImageDto($productVariant->getImages()->first());
            $otherProductVariants[] = new OtherProductVariant(
                publicId: $productVariant->getPublicId(),
                wood: $productVariant->getWoodId()->getName(),
                unitPrice: $productVariant->getPrice(),
                imageUrl: $imageDto->imageUrl,
            );
        }
        $this->logger->debug("ProductService::ProductsVariantsToOtherProductVariantsDto EXIT");
        return $otherProductVariants;
    }

    private function ProductVariantToProductDetailDto(ProductVariant $mainProductVariant, array $otherProductVariants): ProductDetailDto
    {
        $this->logger->debug("ProductService::ProductVariantToProductDetailDto ENTER");
        $dto = new ProductDetailDto(
            shortProduct: new ShortProductDto(
                id: $mainProductVariant->getProductId()->getId(),
                title: $mainProductVariant->getProductId()->getName(),
                type: $mainProductVariant->getStock() != null ? ProductType::IN_STOCK : ProductType::CUSTOM_MADE,
                category: new CategoryDto(
                    name: $mainProductVariant->getProductId()->getCategoryId()->getName(),
                    publicId: new PublicIdDto($mainProductVariant->getProductId()->getCategoryId()->getPublicId())
                ),
                unitPrice: new PriceDto($mainProductVariant->getPrice()),
                mainImage: $this->imageService->imageToImageDto($mainProductVariant->getImages()->first()),
                publicId: new PublicIdDto($mainProductVariant->getPublicId())
            ),
            description: $mainProductVariant->getProductId()->getDescription(),
            stock: $mainProductVariant->getStock(),
            imageUrls: $this->imageService->imagesToImageDtos($mainProductVariant->getImages()->toArray()),
            otherProductVariants: $otherProductVariants
        );

        $this->logger->debug("ProductService::ProductVariantToProductDetailDto EXIT");
        return $dto;
    }

    private function productReviewToProductReviewDto(ProductReview $productReview, string $author): ProductReviewDto
    {
        $this->logger->debug("ProductService::productReviewToProductReviewDto ENTER");

        $sanitizedComment = htmlspecialchars($productReview->getComment(), ENT_QUOTES, 'UTF-8');
        $sanitizedAuthor = htmlspecialchars(trim($author), ENT_QUOTES, 'UTF-8');

        $dto = new ProductReviewDto(
            averageRating: $productReview->getRating(),
            comment: $sanitizedComment,
            authorName: $sanitizedAuthor,
            postedtedAt: $productReview->getCreatedAt()
        );

        $this->logger->debug("ProductService::productReviewToProductReviewDto EXIT");

        return $dto;
    }

    public function requestRatingFiltersDtoBuilder(?string $ratingOrder, ?int $rating, ?string $publicationOrder): RequestRatingFiltersDto
    {
        $this->logger->debug("ProductService::requestRatingFiltersDtoBuilder ENTER");

        $filterRatingOrder = null;
        $filterRating = null;
        $filterPublicationOrder = null;

        if ($rating !== null && $rating >= 1 && $rating <= 5) {
            $filterRating = $rating;
            $filterRatingOrder = CommentSortFilterCode::RATING_AVERAGE_EQUAL;
        } else {
            $tryFromFilterRatingOrder = CommentSortFilterCode::tryFrom($ratingOrder);
            $filterRatingOrder = $tryFromFilterRatingOrder == null ? CommentSortFilterCode::RATING_AVERAGE_DESC : $tryFromFilterRatingOrder;
        }

        if ($publicationOrder !== null) {
            $tryFromFilterPublicationOrder = CommentSortFilterCode::tryFrom($publicationOrder);
            $filterPublicationOrder = $tryFromFilterPublicationOrder == null ? CommentSortFilterCode::POSTED_DESC : $tryFromFilterPublicationOrder;
        }

        $dto = new RequestRatingFiltersDto(
            ratingOrder: $filterRatingOrder,
            rating: $filterRating,
            publicationOrder: $filterPublicationOrder
        );

        $this->logger->debug("ProductService::requestCommentFiltersDtoBuilder EXIT");

        return $dto;
    }
}
