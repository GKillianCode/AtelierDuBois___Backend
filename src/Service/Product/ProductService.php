<?php

namespace App\Service\Product;

use App\Enum\ProductType;
use Psr\Log\LoggerInterface;
use App\Dto\Product\PriceDto;
use App\Dto\Product\PublicIdDto;
use App\Dto\Product\PaginationData;
use App\Dto\Product\ProductDetailDto;
use App\Dto\Product\ShortProductDto;
use App\Service\Product\ImageService;
use App\Entity\Product\ProductVariant;
use App\Enum\SortFilterCode;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Repository\Product\ProductRepository;
use App\Repository\Product\ProductVariantRepository;

class ProductService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProductRepository $productRepository,
        private readonly ProductVariantRepository $productVariantRepository,
        private readonly ImageService $imageService
    ) {}

    public function getAllProducts(int $page, int $limit, SortFilterCode $filter, SortFilterCode $productType): array
    {
        $this->logger->debug("ProductService::getAllProducts ENTER", ['filter' => $filter, 'productType' => $productType]);

        $page = max(1, $page);
        $limit = min(80, max(1, $limit));

        $paginator = $this->productRepository->paginateProducts($page, $limit, $filter, $productType);

        $products = $this->getAllProductsInShortProductDto($paginator);
        $paginationData = $this->getMetaPaginationData($paginator, $limit, $page);

        $this->logger->debug("ProductService::getAllProducts EXIT");

        return [
            'products' => $products,
            'pagination' => $paginationData
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
                title: $product->getName(),
                type: $productType,
                unitPrice: new PriceDto($defaultVariant->getPrice()),
                mainImage: $this->imageService->imageToImageDto($defaultImage),
                publicId: new PublicIdDto($defaultVariant->getPublicId())
            );
        }

        $this->logger->debug("ProductService::getAllProductsInShortProductDto EXIT");

        return $products;
    }

    private function getMetaPaginationData(Paginator $paginator, int $limit, int $page): PaginationData
    {
        $this->logger->debug("ProductService::getMetaPaginationData ENTER");

        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        $paginationData = new PaginationData(
            currentPage: $page,
            totalPages: $totalPages,
            totalItems: $totalItems,
            itemsPerPage: $limit,
            hasNextPage: $page < $totalPages,
            hasPreviousPage: $page > 1
        );

        $this->logger->debug("ProductService::getMetaPaginationData EXIT");

        return $paginationData;
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
                title: $mainProductVariant->getProductId()->getName(),
                type: $mainProductVariant->getStock() != null ? ProductType::IN_STOCK : ProductType::CUSTOM_MADE,
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
}
