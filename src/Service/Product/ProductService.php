<?php

namespace App\Service\Product;

use Psr\Log\LoggerInterface;
use App\Dto\Product\PriceDto;
use App\Dto\Product\PublicIdDto;
use App\Dto\Product\PaginationData;
use App\Dto\Product\ShortProductDto;
use App\Service\Product\ImageService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Repository\Product\ProductRepository;

class ProductService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProductRepository $productRepository,
        private readonly ImageService $imageService
    ) {}

    public function getAllProducts(int $page, int $limit): array
    {
        $this->logger->debug("ProductService::getAllProducts ENTER");

        $page = max(1, $page);
        $limit = min(80, max(1, $limit));

        $paginator = $this->productRepository->paginateProducts($page, $limit);

        $products = $this->getAllProductsInShortProductDto($paginator);
        $paginationData = $this->getMetaPaginationData($paginator, $limit, $page);

        $this->logger->debug("ProductService::getAllProducts EXIT");

        return [
            'products' => $products,
            'pagination' => $paginationData
        ];
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

            $products[] = new ShortProductDto(
                title: $product->getName(),
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
}
