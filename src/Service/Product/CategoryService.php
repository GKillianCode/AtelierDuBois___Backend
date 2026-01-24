<?php

namespace App\Service\Product;

use Psr\Log\LoggerInterface;
use App\Mapper\Product\CategoryMapper;
use App\Manager\Product\CategoryManager;

class CategoryService
{
    public function __construct(
        private readonly CategoryManager $categoryManager,
        private readonly CategoryMapper $categoryMapper,
        private readonly LoggerInterface $logger
    ) {}

    public function getAllCategoriesInCategoryDto(): array
    {
        $this->logger->debug("CategoryService::getAllCategoriesInCategoryDto ENTER");
        $categories = $this->categoryManager->getAllCategories();
        $categories = $this->categoriesToCategoriesDto($categories);
        $this->logger->debug("CategoryService::getAllCategoriesInCategoryDto EXIT");
        return $categories;
    }

    public function categoriesToCategoriesDto(array $categories): array
    {
        $this->logger->debug("CategoryService::categoriesToCategoriesDto ENTER");
        $categoriesDto = [];
        foreach ($categories as $category) {
            $categoriesDto[] = $this->categoryMapper->toDtoFromEntity($category);
        }
        $this->logger->debug("CategoryService::categoriesToCategoriesDto EXIT");
        return $categoriesDto;
    }
}
