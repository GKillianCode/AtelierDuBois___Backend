<?php

namespace App\Controller\Product;

use Psr\Log\LoggerInterface;
use App\Response\ApiResponse;
use App\Service\Product\ProductService;
use App\Mapper\Request\CommentRequestMapper;
use App\Mapper\Request\ProductRequestMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly LoggerInterface $logger,
        private readonly SerializerInterface $serializer,
        private readonly ProductRequestMapper $productRequestMapper,
        private readonly CommentRequestMapper $commentRequestMapper,
    ) {}

    #[Route('/api/public/v1/product/all', name: 'product_get_all', methods: ['GET'])]
    public function getAllProducts(Request $request): Response
    {
        try {
            $this->logger->debug("ProductController::getAllProducts ENTER");

            $getAllProductsRequestDto = $this->productRequestMapper->mapGetAllProductsRequest($request);
            $result = $this->productService->getPaginatedProducts($getAllProductsRequestDto);

            $this->logger->debug("ProductController::getAllProducts EXIT 2");

            return ApiResponse::success($this->serializer->normalize($result));
        } catch (\Exception $e) {
            $this->logger->error("ProductController::getAllProducts ERROR::" . $e->getMessage());

            return ApiResponse::error(
                'An error occurred while fetching products. ' . $e->getMessage(),
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/api/public/v1/product/{publicId}', name: 'product_get_by_publicid', methods: ['GET'])]
    public function getProductByPublicId(string $publicId): Response
    {
        try {
            $this->logger->debug("ProductController::getProductById ENTER with publicId: " . $publicId);

            $product = $this->productService->findVariantWithDetails($publicId);

            if (!$product) {
                $this->logger->debug("ProductController::getProductById EXIT 1");
                return ApiResponse::notFound('Product not found.');
            }
            $this->logger->debug("ProductController::getProductById EXIT 2");

            return ApiResponse::success($this->serializer->normalize($product));
        } catch (\Exception $e) {
            $this->logger->error("ProductController::getProductById ERROR::" . $e->getMessage());
            return ApiResponse::error(
                'An error occurred while fetching the product. ' . $e->getMessage(),
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/api/public/v1/product/{publicId}/reviews', name: 'product_get_reviews_by_publicid', methods: ['GET'])]
    public function getReviewsByVariant(Request $request, string $publicId): Response
    {
        try {
            $this->logger->debug("ProductController::getProductReviewsByProductVariantPublicId ENTER with publicId: " . $publicId);

            $getProductReviewsRequestDto = $this->commentRequestMapper->mapGetAllCommentsRequest($request, $publicId);
            $productsReviewsDto = $this->productService->getProductVariantReviews($getProductReviewsRequestDto);

            $this->logger->debug("ProductController::getProductReviewsByProductVariantPublicId EXIT 2");

            return ApiResponse::success($this->serializer->normalize($productsReviewsDto));
        } catch (\Exception $e) {
            $this->logger->error("ProductController::getProductReviewsByProductVariantPublicId ERROR::" . $e->getMessage());
            return ApiResponse::error(
                'An error occurred while fetching the product. ' . $e->getMessage(),
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
