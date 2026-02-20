<?php

namespace App\Controller\Product;

use App\Response\ApiResponse;
use App\Service\Product\CategoryService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Categories')]
final class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/api/public/v1/category/all', name: 'app_product_category', methods: ['GET'])]
    public function getAllProducts(): Response
    {
        $categoriesDto = $this->categoryService->getAllCategoriesInCategoryDto();
        return ApiResponse::success($this->serializer->normalize($categoriesDto));
    }
}
