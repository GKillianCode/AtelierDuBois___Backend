<?php

namespace App\Service\Product;

use Psr\Log\LoggerInterface;
use App\Dto\Types\CategoryDto;
use App\Dto\Types\PublicIdDto;
use App\Repository\Product\CategoryRepository;

class CategoryService
{
    public function __construct(
        public readonly CategoryRepository $categoryRepository,
        public readonly LoggerInterface $logger
    ) {}

    public function getAllCategoriesInCategoryDto(): array
    {
        $this->logger->debug("CategoryService::getAllCategoriesInCategoryDto ENTER");
        $categories = $this->categoryRepository->findAll();
        $categories = $this->categoriesToCategoriesDto($categories);
        $this->logger->debug("CategoryService::getAllCategoriesInCategoryDto EXIT");
        return $categories;
    }

    public function categoryToCategoryDto($category): CategoryDto
    {
        $categoryDto = new CategoryDto(
            name: $category->getName(),
            publicId: new PublicIdDto($category->getPublicId()),
        );
        return $categoryDto;
    }

    public function categoriesToCategoriesDto(array $categories): array
    {
        $categoriesDto = [];
        foreach ($categories as $category) {
            $categoriesDto[] = $this->categoryToCategoryDto($category);
        }
        return $categoriesDto;
    }
}
