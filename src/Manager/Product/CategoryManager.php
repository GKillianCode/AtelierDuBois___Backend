<?php

namespace App\Manager\Product;

use App\Util\ValidatorUtil;
use Psr\Log\LoggerInterface;
use App\Entity\Product\Category;
use App\Trait\ValidateAndSaveTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Product\CategoryRepository;

class CategoryManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorUtil $validatorUtil
    ) {}

    public function create(): void
    {
        $this->logger->debug("CategoryManager::create ENTER");

        $this->logger->debug("CategoryManager::create EXIT");
    }

    public function update(Category $category): void
    {
        $category->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function delete(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    use ValidateAndSaveTrait;

    public function getAllCategories(): array
    {
        $this->logger->debug("CategoryService::getAllCategoriesInCategoryDto ENTER");
        $categories = $this->categoryRepository->findAll();
        $this->logger->debug("CategoryService::getAllCategoriesInCategoryDto EXIT");
        return $categories;
    }
}
