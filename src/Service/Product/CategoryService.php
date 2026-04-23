<?php

namespace App\Service\Product;

use App\Dto\Types\CategoryDto;
use App\Entity\Product\Category;
use App\Mapper\Product\CategoryMapper;
use App\Manager\Product\CategoryManager;

class CategoryService
{
    public function __construct(
        private readonly CategoryManager $categoryManager,
        private readonly CategoryMapper $categoryMapper,
    ) {}

    /** @return CategoryDto[] */
    public function getAllCategoriesInCategoryDto(): array
    {
        $categories = $this->categoryManager->getAllCategories();
        $categories = $this->categoriesToCategoriesDto($categories);

        return $categories;
    }

    /**
     * @param Category[] $categories
     * @return CategoryDto[]
     */
    public function categoriesToCategoriesDto(array $categories): array
    {
        $categoriesDto = [];
        foreach ($categories as $category) {
            $categoriesDto[] = $this->categoryMapper->toDtoFromEntity($category);
        }

        return $categoriesDto;
    }
}
