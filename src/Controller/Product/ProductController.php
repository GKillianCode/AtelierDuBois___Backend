<?php

namespace App\Controller\Product;

use App\Enum\SortFilterCode;
use App\Service\Product\ProductService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    #[Route('/api/public/v1/product/all', name: 'product_get_all', methods: ['GET'])]
    public function getAllProducts(Request $request): Response
    {
        try {
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 20);
            $filterValue = $request->query->get('filter');
            $productTypeValue = $request->query->get('productType');

            $filter = SortFilterCode::tryFrom($filterValue) ?? SortFilterCode::CREATED_DESC;
            $productType = SortFilterCode::tryFrom($productTypeValue) ?? SortFilterCode::PRODUCTS_ALL;

            $result = $this->productService->getAllProducts($page, $limit, $filter, $productType);

            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while fetching products. ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/public/v1/product/{publicId}', name: 'product_get_by_id', methods: ['GET'])]
    public function getProductById(string $publicId): Response
    {
        try {
            $product = $this->productService->getProductVariantByPublicId($publicId);
            if (!$product) {
                return $this->json([
                    'error' => 'ProductVariant not found.'
                ], Response::HTTP_NOT_FOUND);
            }
            return $this->json($product);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while fetching the product. ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
