<?php

namespace App\Mapper\Product;

use App\Enum\ProductType;
use App\Dto\Types\PriceDto;
use Psr\Log\LoggerInterface;
use App\Entity\Product\Image;
use App\Dto\Types\PublicIdDto;
use App\Entity\Product\ProductVariant;
use App\Dto\Response\ResponseProductDto;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Dto\Response\ResponseResumeProductDto;
use App\Dto\Response\ResponseResumeProductVariantDto;
use App\Entity\Product\Product;

class ProductVariantMapper
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ImageMapper $imageMapper,
        private readonly ProductMapper $productMapper,
        private readonly CategoryMapper $categoryMapper
    ) {}

    public function mapProductsToShortDtos(Paginator $paginator): array
    {
        $this->logger->debug("ProductVariantMapper::mapProductsToShortDtos ENTER");

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

            $products[] = $this->productMapper->toDtoFromEntity($defaultVariant, $defaultImage);
        }

        $this->logger->debug("ProductVariantMapper::mapProductsToShortDtos EXIT");

        return $products;
    }

    public function variantToDto(ProductVariant $mainProductVariant, array $otherProductVariants): ResponseProductDto
    {
        $this->logger->debug("ProductVariantMapper::mapVariantToDetailDto ENTER");

        $images = $mainProductVariant->getImages()->toArray();
        $defaultImage = $this->extractDefaultImage($images);

        $dto = new ResponseProductDto(
            responseResumeProductDto: $this->productMapper->toDtoFromEntity($mainProductVariant, $defaultImage),
            description: $mainProductVariant->getProductId()->getDescription(),
            stock: $mainProductVariant->getStock(),
            wood: $mainProductVariant->getWoodId()->getName(),
            weightInGrams: $mainProductVariant->getProductId()->getWeightInGrams(),
            lengthInCentimeters: $mainProductVariant->getProductId()->getLengthInCentimeters(),
            widthInCentimeters: $mainProductVariant->getProductId()->getWidthInCentimeters(),
            heightInCentimeters: $mainProductVariant->getProductId()->getHeightInCentimeters(),
            imageUrls: array_map(
                fn($image) => $this->imageMapper->toDtoFromEntity($image)->getImageUrl(),
                $mainProductVariant->getImages()->toArray()
            ),
            responseResumeProductVariantDto: $otherProductVariants
        );

        $this->logger->debug("ProductVariantMapper::mapVariantToDetailDto EXIT");
        return $dto;
    }

    private function extractDefaultImage(array $images)
    {
        foreach ($images as $image) {
            if ($image->isDefault()) {
                return $image;
            }
        }
        return null;
    }

    public function mapVariantsToOtherVariantDtos($productsVariants): array
    {
        $this->logger->debug("ProductVariantMapper::mapVariantsToOtherVariantDtos ENTER");

        $variants = [];
        foreach ($productsVariants as $productVariant) {
            $imageDto = $this->imageMapper->toDtoFromEntity($productVariant->getImages()->first());

            $variant = new ResponseResumeProductVariantDto(
                publicId: new PublicIdDto($productVariant->getPublicId()),
                wood: $productVariant->getWoodId()->getName(),
                unitPrice: $productVariant->getPrice() ? new PriceDto($productVariant->getPrice()) : null,
                imageUrl: $imageDto->getImageUrl(),
            );

            $variants[] = $variant;
        }

        $this->logger->debug("ProductVariantMapper::mapVariantsToOtherVariantDtos EXIT");
        return $variants;
    }

    public function toDtoFromProductVariant(ProductVariant $productVariant, Image $image): ResponseResumeProductDto
    {
        $this->logger->debug("ProductMapper::toDtoFromEntity ENTER");

        $product = $productVariant->getProductId();

        $responseResumeProductDto = new ResponseResumeProductDto(
            id: $product->getId(),
            title: $product->getName(),
            type: $productVariant->getStock() != null ? ProductType::IN_STOCK : ProductType::CUSTOM_MADE,
            category: $this->categoryMapper->toDtoFromEntity($product->getCategoryId()),
            unitPrice: $productVariant->getPrice() ? new PriceDto($productVariant->getPrice()) : null,
            mainImage: $this->imageMapper->toDtoFromEntity($image),
            publicId: new PublicIdDto(publicId: $productVariant->getPublicId()),
            averageRating: null,
        );

        $this->logger->debug("ProductMapper::toDtoFromEntity EXIT");
        return $responseResumeProductDto;
    }
}
