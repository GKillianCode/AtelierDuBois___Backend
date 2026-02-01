<?php

namespace App\Controller\Product;

use App\Enum\ErrorCode;
use App\Util\ValidatorUtil;
use Psr\Log\LoggerInterface;
use App\Dto\Types\PublicIdDto;
use App\Response\ErrorResponse;
use App\Manager\Product\ProductManager;
use App\Service\Product\ProductService;
use App\Mapper\Product\ProductReviewMapper;
use Symfony\Component\HttpFoundation\Request;
use App\Enum\SortFilter\ProductSortFilterCode;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Dto\Product\RequestFilter\RequestProductFiltersDto;
use App\Mapper\Request\ProductRequestMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly LoggerInterface $logger,
        private readonly ProductManager $productManager,
        private readonly ProductReviewMapper $productReviewMapper,
        private readonly ProductRequestMapper $productRequestMapper,
    ) {}

    #[Route('/api/public/v1/product/all', name: 'product_get_all', methods: ['GET'])]
    public function getAllProducts(Request $request): Response
    {
        try {
            $this->logger->debug("ProductController::getAllProducts ENTER");

            $getAllProductsRequestDto = $this->productRequestMapper->mapGetAllProductsRequest($request);
            $result = $this->productService->getPaginatedProducts($getAllProductsRequestDto);

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

            $product = $this->productService->findVariantWithDetails($publicId);

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

            $ratingOrderValue = $request->query->get('ratingOrder');
            $ratingValue = (int) $request->query->get('rating');
            $publicationOrderValue = $request->query->get('publicationOrder');

            $requestRatingFiltersDto = $this->productReviewMapper->buildRatingFiltersDto($ratingOrderValue, $ratingValue, $publicationOrderValue);
            $productsReviewsDto = $this->productService->getProductVariantReviews($publicId, $page, $limit, $requestRatingFiltersDto);

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
