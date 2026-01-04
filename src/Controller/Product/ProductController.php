<?php

namespace App\Controller\Product;

use App\Enum\ErrorCode;
use App\Enum\SortFilterCode;
use Psr\Log\LoggerInterface;
use App\Response\ErrorResponse;
use App\Dto\Product\PublicIdDto;
use App\Service\ValidatorService;
use App\Dto\Product\RequestFiltersDto;
use App\Service\Product\ProductService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ValidatorService $validatorService,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/api/public/v1/product/all', name: 'product_get_all', methods: ['GET'])]
    public function getAllProducts(Request $request): Response
    {
        try {
            $this->logger->debug("ProductController::getAllProducts ENTER");

            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 20);
            $search = $request->query->get('search', '');
            $filterValue = $request->query->get('filter');
            $productTypeValue = $request->query->get('productType');
            $categoryValue = $request->query->get('category');

            $filter = SortFilterCode::tryFrom($filterValue) ?? SortFilterCode::CREATED_DESC;
            $productType = SortFilterCode::tryFrom($productTypeValue) ?? SortFilterCode::PRODUCTS_ALL;

            $categoryPublicIdDto = $categoryValue !== null
                ? new PublicIdDto(publicId: $categoryValue)
                : null;

            if ($categoryPublicIdDto !== null && $this->validatorService->hasViolations($categoryPublicIdDto)) {
                $this->logger->debug("ProductController::getAllProducts EXIT 1 - Invalid category format");
                return $this->createErrorResponse(
                    ErrorCode::INVALID_DATA,
                    'Invalid category format. Expected 22 characters in base62 format.',
                    "Format de catégorie invalide.",
                );
            }

            $requestFiltersDto = new RequestFiltersDto(
                search: $search,
                filter: $filter,
                productType: $productType,
                categoryPublicId: $categoryPublicIdDto,
            );

            $result = $this->productService->getAllProducts($page, $limit, $requestFiltersDto);

            $this->logger->debug("ProductController::getAllProducts EXIT 2");
            return $this->json($result, Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error("ProductController::getAllProducts ERROR::" . $e->getMessage());
            return $this->json([
                'error' => 'An error occurred while fetching products. ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/public/v1/product/{publicId}', name: 'product_get_by_publicid', methods: ['GET'])]
    public function getProductById(string $publicId): Response
    {
        try {
            $this->logger->debug("ProductController::getProductById ENTER with publicId: " . $publicId);
            $product = $this->productService->getProductVariantByPublicId($publicId);
            if (!$product) {
                $this->logger->debug("ProductController::getProductById EXIT 1");
                return $this->createErrorResponse(
                    ErrorCode::PRODUCT_NOT_FOUND,
                    'Product not found.',
                    "Produit non trouvé."
                );
            }
            $this->logger->debug("ProductController::getProductById EXIT 2");
            return $this->json($product, Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error("ProductController::getProductById ERROR::" . $e->getMessage());
            return $this->json([
                'error' => 'An error occurred while fetching the product. ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/public/v1/product/{publicId}/reviews', name: 'product_get_all_reviews_by_publicid', methods: ['GET'])]
    public function getProductReviewsByProductVariantPublicId(Request $request, string $publicId): Response
    {
        try {
            $this->logger->debug("ProductController::getProductReviewsByProductVariantPublicId ENTER with publicId: " . $publicId);
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 10);

            $productsReviewsDto = $this->productService->getProductReviewsByProductVariantPublicId($publicId, $page, $limit);

            $this->logger->debug("ProductController::getProductReviewsByProductVariantPublicId EXIT 2");
            return $this->json($productsReviewsDto, Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error("ProductController::getProductReviewsByProductVariantPublicId ERROR::" . $e->getMessage());
            return $this->json([
                'error' => 'An error occurred while fetching the product. ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createErrorResponse(ErrorCode $code, string $message, string $userMessage, array $details = []): JsonResponse
    {
        $errorResponse = new ErrorResponse($code->value, $message, $details, $userMessage);
        return new JsonResponse($errorResponse->toArray(), Response::HTTP_BAD_REQUEST);
    }
}
