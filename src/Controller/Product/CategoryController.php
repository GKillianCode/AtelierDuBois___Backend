<?php

namespace App\Controller\Product;

use Psr\Log\LoggerInterface;
use App\Service\Product\CategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService,
        public readonly LoggerInterface $logger,
    ) {}

    #[Route('/api/public/v1/category/all', name: 'app_product_category', methods: ['GET'])]
    public function getAllProducts(): Response
    {
        try {
            $this->logger->debug("CategoryController::getAllProducts ENTER");
            $categoriesDto = $this->categoryService->getAllCategoriesInCategoryDto();

            $this->logger->debug("CategoryController::getAllProducts EXIT");
            return $this->json($categoriesDto, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while fetching products. ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
