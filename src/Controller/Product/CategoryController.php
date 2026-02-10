<?php

namespace App\Controller\Product;

use App\Response\ApiResponse;
use Psr\Log\LoggerInterface;
use App\Service\Product\CategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService,
        public readonly LoggerInterface $logger,
        public readonly SerializerInterface $serializer,
    ) {}

    #[Route('/api/public/v1/category/all', name: 'app_product_category', methods: ['GET'])]
    public function getAllProducts(): Response
    {
        try {
            $this->logger->debug("CategoryController::getAllProducts ENTER");
            $categoriesDto = $this->categoryService->getAllCategoriesInCategoryDto();
            $this->logger->debug("CategoryController::getAllProducts EXIT");

            return ApiResponse::success($this->serializer->normalize($categoriesDto));
        } catch (\Exception $e) {
            return ApiResponse::error(
                'An error occurred while fetching categories. ' . $e->getMessage(),
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
