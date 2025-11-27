<?php

namespace App\Controller\Product;

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

    #[Route('/api/v1/product/all', name: 'product_get_all', methods: ['GET'])]
    public function getAllProducts(Request $request): Response
    {
        try {
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 20);

            $result = $this->productService->getAllProducts($page, $limit);

            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while fetching products. ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
