<?php

namespace App\Tests\Controller\Product;

use App\Service\Product\ProductService;
use App\Service\ValidatorService;
use App\Dto\Product\ShortProductDto;
use App\Dto\Product\ProductDetailDto;
use App\Dto\Product\ProductReviewDto;
use App\Dto\Product\RequestFilter\RequestRatingFiltersDto;
use App\Dto\Types\PublicIdDto;
use App\Dto\Types\CategoryDto;
use App\Dto\Types\PriceDto;
use App\Dto\Types\ImageDto;
use App\Dto\Types\PaginationDataDto;
use App\Enum\ProductType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    // Tests pour getAllProducts
    public function testGetAllProductsSuccess(): void
    {
        $productService = $this->createMock(ProductService::class);

        $shortProduct = new ShortProductDto(
            id: 1,
            title: 'Table en bois',
            type: ProductType::IN_STOCK,
            category: new CategoryDto(
                name: 'Meubles',
                publicId: new PublicIdDto('cat123abcdefghijklmnop')
            ),
            unitPrice: new PriceDto(15000),
            mainImage: new ImageDto('http://example.com/image.jpg'),
            publicId: new PublicIdDto('prod123abcdefghijklmno'),
            averageRating: 5
        );

        $paginationData = new PaginationDataDto(
            currentPage: 1,
            totalPages: 1,
            totalItems: 1,
            itemsPerPage: 20,
            hasNextPage: false,
            hasPreviousPage: false
        );

        $expectedResult = [
            'products' => [$shortProduct],
            'pagination' => $paginationData
        ];

        $productService
            ->expects($this->once())
            ->method('getAllProducts')
            ->willReturn($expectedResult);

        // Remplacer le service dans le conteneur
        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/all');

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('products', $content);
        $this->assertArrayHasKey('pagination', $content);
    }

    public function testGetAllProductsWithParameters(): void
    {
        $productService = $this->createMock(ProductService::class);
        $validatorService = $this->createMock(ValidatorService::class);

        $validatorService
            ->expects($this->once())
            ->method('hasViolations')
            ->willReturn(false);

        $productService
            ->expects($this->once())
            ->method('getAllProducts')
            ->willReturn(['products' => [], 'pagination' => null]);

        self::getContainer()->set(ProductService::class, $productService);
        self::getContainer()->set(ValidatorService::class, $validatorService);

        $this->client->request('GET', '/api/public/v1/product/all', [
            'page' => '2',
            'limit' => '10',
            'search' => 'table',
            'filter' => 'PRICE_ASC',
            'productType' => 'PRODUCTS_WITH_PRICE',
            'category' => 'cat123abcdefghijklmnop'
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testGetAllProductsWithInvalidCategory(): void
    {
        $validatorService = $this->createMock(ValidatorService::class);
        $validatorService
            ->expects($this->once())
            ->method('hasViolations')
            ->willReturn(true);

        self::getContainer()->set(ValidatorService::class, $validatorService);

        $this->client->request('GET', '/api/public/v1/product/all', [
            'category' => 'invalid-category'
        ]);

        $this->assertResponseStatusCodeSame(400);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('INVALID_DATA', $content['error']['code']);
    }

    public function testGetAllProductsDefault(): void
    {
        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('getAllProducts')
            ->with(1, 20, $this->anything())
            ->willReturn(['products' => [], 'pagination' => null]);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/all');

        $this->assertResponseIsSuccessful();
    }

    // Tests pour getProductById
    public function testGetProductByIdSuccess(): void
    {
        $productService = $this->createMock(ProductService::class);

        $productDetail = new ProductDetailDto(
            shortProduct: new ShortProductDto(
                id: 1,
                title: 'Table en bois',
                type: ProductType::IN_STOCK,
                category: new CategoryDto(
                    name: 'Meubles',
                    publicId: new PublicIdDto('cat123abcdefghijklmnop')
                ),
                unitPrice: new PriceDto(15000),
                mainImage: new ImageDto('http://example.com/image.jpg'),
                publicId: new PublicIdDto('prod123abcdefghijklmno')
            ),
            description: 'Belle table en bois massif',
            stock: 5,
            imageUrls: [new ImageDto('http://example.com/image.jpg')],
            otherProductVariants: []
        );

        $productService
            ->expects($this->once())
            ->method('getProductVariantByPublicId')
            ->with('prod123abcdefghijklmno')
            ->willReturn($productDetail);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/prod123abcdefghijklmno');

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Table en bois', $content['title']);
    }

    public function testGetProductByIdNotFound(): void
    {
        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('getProductVariantByPublicId')
            ->with('nonexistent')
            ->willReturn(null);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/nonexistent');

        $this->assertResponseStatusCodeSame(400);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('PRODUCT_NOT_FOUND', $content['error']['code']);
    }

    // Tests pour getProductReviewsByProductVariantPublicId
    public function testGetProductReviewsSuccess(): void
    {
        $requestRatingFiltersDto = $this->createMock(RequestRatingFiltersDto::class);
        $productService = $this->createMock(ProductService::class);

        $review = new ProductReviewDto(
            averageRating: 5,
            comment: 'Excellent produit!',
            authorName: 'John Doe',
            postedtedAt: new \DateTimeImmutable()
        );

        $paginationData = new PaginationDataDto(
            currentPage: 1,
            totalPages: 1,
            totalItems: 1,
            itemsPerPage: 10,
            hasNextPage: false,
            hasPreviousPage: false
        );

        $expectedResult = [
            'products' => [$review],
            'pagination' => $paginationData
        ];

        $productService
            ->expects($this->once())
            ->method('requestRatingFiltersDtoBuilder')
            ->willReturn($requestRatingFiltersDto);

        $productService
            ->expects($this->once())
            ->method('getProductReviewsByProductVariantPublicId')
            ->willReturn($expectedResult);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/prod123abcdefghijklmno/reviews');

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('products', $content);
        $this->assertArrayHasKey('pagination', $content);
    }

    public function testGetProductReviewsWithFilters(): void
    {
        $requestRatingFiltersDto = $this->createMock(RequestRatingFiltersDto::class);
        $productService = $this->createMock(ProductService::class);

        $productService
            ->expects($this->once())
            ->method('requestRatingFiltersDtoBuilder')
            ->with('RATING_AVERAGE_ASC', 4, 'POSTED_DESC')
            ->willReturn($requestRatingFiltersDto);

        $productService
            ->expects($this->once())
            ->method('getProductReviewsByProductVariantPublicId')
            ->willReturn(['products' => [], 'pagination' => null]);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/prod123abcdefghijklmno/reviews', [
            'page' => '2',
            'limit' => '5',
            'ratingOrder' => 'RATING_AVERAGE_ASC',
            'rating' => '4',
            'publicationOrder' => 'POSTED_DESC'
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testGetProductReviewsDefault(): void
    {
        $requestRatingFiltersDto = $this->createMock(RequestRatingFiltersDto::class);
        $productService = $this->createMock(ProductService::class);

        $productService
            ->expects($this->once())
            ->method('requestRatingFiltersDtoBuilder')
            ->with(null, 0, null)
            ->willReturn($requestRatingFiltersDto);

        $productService
            ->expects($this->once())
            ->method('getProductReviewsByProductVariantPublicId')
            ->with('prod123abcdefghijklmno', 1, 10, $requestRatingFiltersDto)
            ->willReturn(['products' => [], 'pagination' => null]);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/prod123abcdefghijklmno/reviews');

        $this->assertResponseIsSuccessful();
    }

    // Tests d'erreur
    public function testGetAllProductsInternalError(): void
    {
        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('getAllProducts')
            ->willThrowException(new \Exception('Database error'));

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/all');

        $this->assertResponseStatusCodeSame(500);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertStringContainsString('Database error', $content['error']);
    }

    public function testGetProductByIdInternalError(): void
    {
        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('getProductVariantByPublicId')
            ->willThrowException(new \Exception('Database error'));

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/prod123abcdefghijklmno');

        $this->assertResponseStatusCodeSame(500);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertStringContainsString('Database error', $content['error']);
    }

    public function testGetProductReviewsInternalError(): void
    {
        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('requestRatingFiltersDtoBuilder')
            ->willThrowException(new \Exception('Service error'));

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/prod123abcdefghijklmno/reviews');

        $this->assertResponseStatusCodeSame(500);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertStringContainsString('Service error', $content['error']);
    }

    // Tests edge cases
    public function testGetAllProductsWithNegativeValues(): void
    {
        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('getAllProducts')
            ->with(-5, -10, $this->anything())
            ->willReturn(['products' => [], 'pagination' => null]);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/all', [
            'page' => '-5',
            'limit' => '-10'
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testGetAllProductsWithZeroValues(): void
    {
        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('getAllProducts')
            ->with(0, 0, $this->anything())
            ->willReturn(['products' => [], 'pagination' => null]);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/all', [
            'page' => '0',
            'limit' => '0'
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testGetAllProductsWithEmptySearch(): void
    {
        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('getAllProducts')
            ->willReturn(['products' => [], 'pagination' => null]);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/all', [
            'search' => ''
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testGetAllProductsWithInvalidEnumValues(): void
    {
        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('getAllProducts')
            ->willReturn(['products' => [], 'pagination' => null]);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/all', [
            'filter' => 'INVALID_FILTER',
            'productType' => 'INVALID_TYPE'
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testGetProductReviewsWithInvalidRating(): void
    {
        $requestRatingFiltersDto = $this->createMock(RequestRatingFiltersDto::class);

        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('requestRatingFiltersDtoBuilder')
            ->with(null, -1, null)
            ->willReturn($requestRatingFiltersDto);

        $productService
            ->expects($this->once())
            ->method('getProductReviewsByProductVariantPublicId')
            ->willReturn(['products' => [], 'pagination' => null]);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/prod123abcdefghijklmno/reviews', [
            'rating' => '-1'
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testGetProductReviewsWithHighRating(): void
    {
        $requestRatingFiltersDto = $this->createMock(RequestRatingFiltersDto::class);

        $productService = $this->createMock(ProductService::class);
        $productService
            ->expects($this->once())
            ->method('requestRatingFiltersDtoBuilder')
            ->with(null, 10, null)
            ->willReturn($requestRatingFiltersDto);

        $productService
            ->expects($this->once())
            ->method('getProductReviewsByProductVariantPublicId')
            ->willReturn(['products' => [], 'pagination' => null]);

        self::getContainer()->set(ProductService::class, $productService);

        $this->client->request('GET', '/api/public/v1/product/prod123abcdefghijklmno/reviews', [
            'rating' => '10'
        ]);

        $this->assertResponseIsSuccessful();
    }
}
