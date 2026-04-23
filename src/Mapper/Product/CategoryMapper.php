<?php

namespace App\Mapper\Product;

use Psr\Log\LoggerInterface;
use App\Dto\Types\CategoryDto;
use App\Dto\Types\PublicIdDto;
use App\Entity\Product\Category;

class CategoryMapper
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function toDtoFromEntity(Category $category): CategoryDto
    {
        $this->logger->debug("CategoryMapper::toDtoFromEntity ENTER");

        $categoryDto = new CategoryDto(
            name: $category->getName(),
            publicId: new PublicIdDto($category->getPublicId()),
        );

        $this->logger->debug("CategoryMapper::toDtoFromEntity EXIT");
        return $categoryDto;
    }
}
