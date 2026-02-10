<?php

namespace App\Mapper\Product;

use App\Dto\Types\PriceDto;
use App\Dto\Types\PublicIdDto;
use App\Enum\ProductType;
use Psr\Log\LoggerInterface;
use App\Entity\Product\Image;
use App\Entity\Product\ProductVariant;
use App\Mapper\Product\CategoryMapper;
use App\Dto\Response\ResponseResumeProductDto;

class ProductMapper
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ImageMapper $imageMapper,
        private readonly CategoryMapper $categoryMapper
    ) {}

    /**
     * Converts a ProductVariant and its image to product summary DTO
     *
     * @param ProductVariant $productVariant The product variant entity
     * @param Image $image The product image entity
     * @return ResponseResumeProductDto The product summary DTO
     */
    public function toDtoFromEntity(ProductVariant $productVariant, Image $image): ResponseResumeProductDto
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
