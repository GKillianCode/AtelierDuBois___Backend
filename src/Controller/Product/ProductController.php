<?php

namespace App\Controller\Product;

use App\Response\ApiResponse;
use OpenApi\Attributes as OA;
use App\Service\Product\ProductService;
use App\Mapper\Request\CommentRequestMapper;
use App\Mapper\Request\ProductRequestMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: 'Products')]
final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly SerializerInterface $serializer,
        private readonly ProductRequestMapper $productRequestMapper,
        private readonly CommentRequestMapper $commentRequestMapper,
    ) {}

    #[Route('/api/public/v1/product/all', name: 'product_get_all', methods: ['GET'])]
    public function getAllProducts(Request $request): Response
    {
        $getAllProductsRequestDto = $this->productRequestMapper->mapGetAllProductsRequest($request);
        $result = $this->productService->getPaginatedProducts($getAllProductsRequestDto);

        return ApiResponse::success($this->serializer->normalize($result));
    }

    #[Route('/api/public/v1/product/{publicId}', name: 'product_get_by_publicid', methods: ['GET'])]
    public function getProductByPublicId(string $publicId): Response
    {
        $product = $this->productService->findVariantWithDetails($publicId);

        return ApiResponse::success($this->serializer->normalize($product));
    }

    #[Route('/api/public/v1/product/{publicId}/reviews', name: 'product_get_reviews_by_publicid', methods: ['GET'])]
    public function getReviewsByVariant(Request $request, string $publicId): Response
    {
        $getProductReviewsRequestDto = $this->commentRequestMapper->mapGetAllCommentsRequest($request, $publicId);
        $productsReviewsDto = $this->productService->getProductVariantReviews($getProductReviewsRequestDto);

        return ApiResponse::success($this->serializer->normalize($productsReviewsDto));
    }
}
