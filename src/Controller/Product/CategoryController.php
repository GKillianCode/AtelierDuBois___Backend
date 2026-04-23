<?php

namespace App\Controller\Product;

use App\Dto\OpenApiModel\CategoryOAModel;
use App\Response\ApiResponse;
use App\Service\Product\CategoryService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[OA\Tag(name: 'Categories')]
final class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly NormalizerInterface $serializer,
    ) {}

    #[Route('/api/public/v1/category/all', name: 'app_product_category', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get all product categories',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Categories retrieved successfully',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: CategoryOAModel::class))
        )
    )]
    public function getAllCategories(): Response
    {
        $categoriesDto = $this->categoryService->getAllCategoriesInCategoryDto();
        return ApiResponse::success($this->serializer->normalize($categoriesDto));
    }
}
