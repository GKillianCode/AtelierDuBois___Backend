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

    public function update(Category $category): void
    {
        try {
            $category->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error updating category',
                [
                    'exception' => $e->getMessage(),
                    'categoryId' => $category->getId()
                ]
            );
        }
    }

    public function delete(Category $category): void
    {
        try {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error deleting category',
                [
                    'exception' => $e->getMessage(),
                    'categoryId' => $category->getId()
                ]
            );
        }
    }

    use ValidateAndSaveTrait;

    /** @return Category[] */
    public function getAllCategories(): array
    {
        $categories = $this->categoryRepository->findAll();
        return $categories;
    }
}
