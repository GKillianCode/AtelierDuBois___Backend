<?php

namespace App\Controller\Product;

use App\Dto\OpenApiModel\ProductOAModel;
use App\Dto\OpenApiModel\ProductResumeOAModel;
use App\Dto\OpenApiModel\ReviewOAModel;
use App\Dto\Types\PaginationDataDto;
use App\Mapper\Request\CommentRequestMapper;
use App\Mapper\Request\ProductRequestMapper;
use App\Response\ApiResponse;
use App\Service\Product\ProductService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[OA\Tag(name: 'Products')]
final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly NormalizerInterface $serializer,
        private readonly ProductRequestMapper $productRequestMapper,
        private readonly CommentRequestMapper $commentRequestMapper,
    ) {}

    #[Route('/api/public/v1/product/all', name: 'product_get_all', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get all products with pagination and optional filters',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Products retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'products',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ProductResumeOAModel::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationDataDto::class)
                )
            ],
            type: 'object'
        )
    )]
    public function getAllProducts(Request $request): Response
    {
        $getAllProductsRequestDto = $this->productRequestMapper->mapGetAllProductsRequest($request);
        $result = $this->productService->getPaginatedProducts($getAllProductsRequestDto);

        return ApiResponse::success($this->serializer->normalize($result));
    }

    #[Route('/api/public/v1/product/{publicId}', name: 'product_get_by_publicid', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get a product by its public ID',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Product retrieved successfully',
        content: new OA\JsonContent(
            ref: new Model(type: ProductOAModel::class)
        )
    )]
    public function getProductByPublicId(string $publicId): Response
    {
        $product = $this->productService->findVariantWithDetails($publicId);

        return ApiResponse::success($this->serializer->normalize($product));
    }

    #[Route('/api/public/v1/product/{publicId}/reviews', name: 'product_get_reviews_by_publicid', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get reviews for a product variant by its public ID',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Reviews retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'reviews',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: ReviewOAModel::class))
                ),
                new OA\Property(
                    property: 'pagination',
                    ref: new Model(type: PaginationDataDto::class)
                )
            ],
            type: 'object'
        )
    )]
    public function getReviewsByVariant(Request $request, string $publicId): Response
    {
        $getProductReviewsRequestDto = $this->commentRequestMapper->mapGetAllCommentsRequest($request, $publicId);
        $productsReviewsDto = $this->productService->getProductVariantReviews($getProductReviewsRequestDto);

        return ApiResponse::success($this->serializer->normalize($productsReviewsDto));
    }
}
