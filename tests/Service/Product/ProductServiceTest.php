<?php

namespace App\Tests\Service\Product;

use App\Entity\User\User;
use App\Entity\Product\Product;
use App\Entity\Product\Image;
use App\Entity\Product\Wood;
use App\Entity\Product\Category;
use App\Entity\Product\ProductVariant;
use App\Entity\Product\ProductReview;
use App\Service\Product\ProductService;
use App\Service\Product\ImageService;
use App\Service\PaginationService;
use App\Repository\Product\ProductRepository;
use App\Repository\Product\ProductVariantRepository;
use App\Repository\Product\ProductReviewRepository;
use App\Dto\Product\RequestFilter\RequestProductFiltersDto;
use App\Dto\Product\RequestFilter\RequestRatingFiltersDto;
use App\Dto\Product\ShortProductDto;
use App\Dto\Product\ProductDetailDto;
use App\Dto\Product\ProductReviewDto;
use App\Dto\Types\ImageDto;
use App\Dto\Types\PaginationDataDto;
use App\Enum\ProductType;
use App\Enum\SortFilter\ProductSortFilterCode;
use App\Enum\SortFilter\CommentSortFilterCode;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\ArrayCollection;

class ProductServiceTest extends TestCase
{
    private ProductService $productService;
    private MockObject $logger;
    private MockObject $productRepository;
    private MockObject $productVariantRepository;
    private MockObject $productReviewRepository;
    private MockObject $imageService;
    private MockObject $paginationService;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->productVariantRepository = $this->createMock(ProductVariantRepository::class);
        $this->productReviewRepository = $this->createMock(ProductReviewRepository::class);
        $this->imageService = $this->createMock(ImageService::class);
        $this->paginationService = $this->createMock(PaginationService::class);

        $this->productService = new ProductService(
            $this->logger,
            $this->productRepository,
            $this->productVariantRepository,
            $this->productReviewRepository,
            $this->imageService,
            $this->paginationService
        );
    }

    // Tests pour getAllProducts
    public function testGetAllProducts(): void
    {
        $page = 1;
        $limit = 10;
        $requestFiltersDto = new RequestProductFiltersDto(
            search: 'table',
            filter: ProductSortFilterCode::PRICE_ASC,
            productType: ProductSortFilterCode::PRODUCTS_ALL,
            categoryPublicId: null
        );

        // Mock du produit et de ses relations
        $category = $this->createMock(Category::class);
        $category->method('getName')->willReturn('Meubles');
        $category->method('getPublicId')->willReturn('cat123');

        $image = $this->createMock(Image::class);
        $imageCollection = new ArrayCollection([$image]);

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getPrice')->willReturn(15000);
        $productVariant->method('getStock')->willReturn(5);
        $productVariant->method('getPublicId')->willReturn('variant123');
        $productVariant->method('getImages')->willReturn($imageCollection);

        $productVariantCollection = new ArrayCollection([$productVariant]);

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Table en bois');
        $product->method('getCategoryId')->willReturn($category);
        $product->method('getProductVariants')->willReturn($productVariantCollection);

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$product]));

        $this->productRepository
            ->expects($this->once())
            ->method('paginateProducts')
            ->with($page, $limit, $requestFiltersDto)
            ->willReturn($paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('getAverageRatingsForProducts')
            ->willReturn([1 => 4.5]);

        $imageDto = new ImageDto('http://example.com/image.jpg');
        $this->imageService
            ->expects($this->once())
            ->method('imageToImageDto')
            ->with($image)
            ->willReturn($imageDto);

        $paginationDto = new PaginationDataDto(
            currentPage: 1,
            totalPages: 1,
            totalItems: 1,
            itemsPerPage: 10,
            hasNextPage: false,
            hasPreviousPage: false
        );

        $this->paginationService
            ->expects($this->once())
            ->method('getMetaPaginationData')
            ->with($paginator, $limit, $page)
            ->willReturn($paginationDto);

        $result = $this->productService->getAllProducts($page, $limit, $requestFiltersDto);

        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertCount(1, $result['products']);
        $this->assertInstanceOf(ShortProductDto::class, $result['products'][0]);
        $this->assertEquals(5, $result['products'][0]->averageRating); // rounded 4.5 to 5
        $this->assertSame($paginationDto, $result['pagination']);
    }

    public function testGetAllProductsWithPageAndLimitConstraints(): void
    {
        $requestFiltersDto = new RequestProductFiltersDto(
            search: '',
            filter: ProductSortFilterCode::PRICE_ASC,
            productType: ProductSortFilterCode::PRODUCTS_ALL,
            categoryPublicId: null
        );

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));

        $this->productRepository
            ->expects($this->once())
            ->method('paginateProducts')
            ->with(1, 80, $requestFiltersDto) // page max 1, limit max 80
            ->willReturn($paginator);

        $this->productRepository
            ->expects($this->once())
            ->method('getAverageRatingsForProducts')
            ->willReturn([]);

        $paginationDto = new PaginationDataDto(
            currentPage: 1,
            totalPages: 0,
            totalItems: 0,
            itemsPerPage: 80,
            hasNextPage: false,
            hasPreviousPage: false
        );

        $this->paginationService
            ->expects($this->once())
            ->method('getMetaPaginationData')
            ->willReturn($paginationDto);

        // Test avec des valeurs extrêmes
        $result = $this->productService->getAllProducts(-5, 100, $requestFiltersDto);

        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertCount(0, $result['products']);
    }

    // Tests pour getProductReviewsByProductVariantPublicId
    public function testGetProductReviewsByProductVariantPublicIdFound(): void
    {
        $publicId = 'variant123';
        $page = 1;
        $limit = 5;
        $requestRatingFiltersDto = new RequestRatingFiltersDto(
            ratingOrder: CommentSortFilterCode::RATING_AVERAGE_DESC,
            rating: null,
            publicationOrder: null
        );

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getId')->willReturn(1);

        $this->productVariantRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['publicId' => $publicId])
            ->willReturn($productVariant);

        $user = $this->createMock(User::class);
        $user->method('getFirstName')->willReturn('John');
        $user->method('getLastName')->willReturn('Doe');

        $review = $this->createMock(ProductReview::class);
        $review->method('getUserId')->willReturn($user);
        $review->method('getRating')->willReturn(5);
        $review->method('getComment')->willReturn('Excellent produit!');
        $review->method('getCreatedAt')->willReturn(new \DateTimeImmutable());

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$review]));

        $this->productReviewRepository
            ->expects($this->once())
            ->method('paginateProductReviews')
            ->with($page, $limit, 1, $requestRatingFiltersDto)
            ->willReturn($paginator);

        $paginationDto = new PaginationDataDto(
            currentPage: 1,
            totalPages: 1,
            totalItems: 1,
            itemsPerPage: 5,
            hasNextPage: false,
            hasPreviousPage: false
        );

        $this->paginationService
            ->expects($this->once())
            ->method('getMetaPaginationData')
            ->willReturn($paginationDto);

        $result = $this->productService->getProductReviewsByProductVariantPublicId($publicId, $page, $limit, $requestRatingFiltersDto);

        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertCount(1, $result['products']);
        $this->assertInstanceOf(ProductReviewDto::class, $result['products'][0]);
    }

    public function testGetProductReviewsByProductVariantPublicIdNotFound(): void
    {
        $publicId = 'nonexistent';
        $page = 1;
        $limit = 5;
        $requestRatingFiltersDto = new RequestRatingFiltersDto(
            ratingOrder: CommentSortFilterCode::RATING_AVERAGE_DESC,
            rating: null,
            publicationOrder: null
        );

        $this->productVariantRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['publicId' => $publicId])
            ->willReturn(null);

        $result = $this->productService->getProductReviewsByProductVariantPublicId($publicId, $page, $limit, $requestRatingFiltersDto);

        $this->assertEquals([
            'reviews' => [],
            'pagination' => null
        ], $result);
    }

    public function testGetProductReviewsByProductVariantPublicIdWithPageAndLimitConstraints(): void
    {
        $publicId = 'variant123';
        $requestRatingFiltersDto = new RequestRatingFiltersDto(
            ratingOrder: CommentSortFilterCode::RATING_AVERAGE_DESC,
            rating: null,
            publicationOrder: null
        );

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getId')->willReturn(1);

        $this->productVariantRepository
            ->method('findOneBy')
            ->willReturn($productVariant);

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));

        $this->productReviewRepository
            ->expects($this->once())
            ->method('paginateProductReviews')
            ->with(1, 10, 1, $requestRatingFiltersDto) // page max 1, limit max 10
            ->willReturn($paginator);

        $this->paginationService
            ->method('getMetaPaginationData')
            ->willReturn(new PaginationDataDto(1, 0, 0, 10, false, false));

        // Test avec des valeurs extrêmes
        $result = $this->productService->getProductReviewsByProductVariantPublicId($publicId, -5, 20, $requestRatingFiltersDto);

        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('pagination', $result);
    }

    // Tests pour getProductVariantByPublicId
    public function testGetProductVariantByPublicIdFound(): void
    {
        $publicId = 'variant123';

        $category = $this->createMock(Category::class);
        $category->method('getName')->willReturn('Meubles');
        $category->method('getPublicId')->willReturn('cat123');

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Table en bois');
        $product->method('getDescription')->willReturn('Belle table en bois massif');
        $product->method('getCategoryId')->willReturn($category);

        $image = $this->createMock(Image::class);
        $imageCollection = new ArrayCollection([$image]);

        $wood = $this->createMock(Wood::class);
        $wood->method('getName')->willReturn('Chêne');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getProductId')->willReturn($product);
        $productVariant->method('getPrice')->willReturn(25000);
        $productVariant->method('getStock')->willReturn(3);
        $productVariant->method('getPublicId')->willReturn($publicId);
        $productVariant->method('getImages')->willReturn($imageCollection);
        $productVariant->method('getWoodId')->willReturn($wood);

        $this->productVariantRepository
            ->expects($this->once())
            ->method('getProductVariantByPublicId')
            ->with($publicId)
            ->willReturn($productVariant);

        $otherVariant = $this->createMock(ProductVariant::class);
        $otherVariant->method('getPublicId')->willReturn('other123');
        $otherVariant->method('getPrice')->willReturn(30000);
        $otherVariant->method('getWoodId')->willReturn($wood);
        $otherVariant->method('getImages')->willReturn($imageCollection);

        $this->productVariantRepository
            ->expects($this->once())
            ->method('getAllMinimalProductVariant')
            ->with(1)
            ->willReturn([$productVariant, $otherVariant]);

        $imageDto = new ImageDto('http://example.com/image.jpg');
        $this->imageService
            ->method('imageToImageDto')
            ->willReturn($imageDto);

        $this->imageService
            ->method('imagesToImageDtos')
            ->willReturn([$imageDto]);

        $result = $this->productService->getProductVariantByPublicId($publicId);

        $this->assertInstanceOf(ProductDetailDto::class, $result);
        $this->assertEquals('Table en bois', $result->shortProduct->title);
        $this->assertEquals('Belle table en bois massif', $result->description);
        $this->assertEquals(3, $result->stock);
        $this->assertCount(2, $result->otherProductVariants);
    }

    public function testGetProductVariantByPublicIdNotFound(): void
    {
        $publicId = 'nonexistent';

        $this->productVariantRepository
            ->expects($this->once())
            ->method('getProductVariantByPublicId')
            ->with($publicId)
            ->willReturn(null);

        $result = $this->productService->getProductVariantByPublicId($publicId);

        $this->assertNull($result);
    }

    public function testGetProductVariantByPublicIdWithNoOtherVariants(): void
    {
        $publicId = 'variant123';

        $productVariant = $this->createMock(ProductVariant::class);
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $productVariant->method('getProductId')->willReturn($product);

        $this->productVariantRepository
            ->method('getProductVariantByPublicId')
            ->willReturn($productVariant);

        $this->productVariantRepository
            ->method('getAllMinimalProductVariant')
            ->willReturn([]);

        $result = $this->productService->getProductVariantByPublicId($publicId);

        $this->assertNull($result);
    }

    // Tests pour requestRatingFiltersDtoBuilder
    public function testRequestRatingFiltersDtoBuilderWithValidRating(): void
    {
        $result = $this->productService->requestRatingFiltersDtoBuilder(null, 4, null);

        $this->assertInstanceOf(RequestRatingFiltersDto::class, $result);
        $this->assertEquals(CommentSortFilterCode::RATING_AVERAGE_EQUAL, $result->ratingOrder);
        $this->assertEquals(4, $result->rating);
        $this->assertNull($result->publicationOrder);
    }

    public function testRequestRatingFiltersDtoBuilderWithInvalidRating(): void
    {
        $result = $this->productService->requestRatingFiltersDtoBuilder('RATING_AVERAGE_ASC', 6, null);

        $this->assertInstanceOf(RequestRatingFiltersDto::class, $result);
        $this->assertEquals(CommentSortFilterCode::RATING_AVERAGE_ASC, $result->ratingOrder);
        $this->assertNull($result->rating);
        $this->assertNull($result->publicationOrder);
    }

    public function testRequestRatingFiltersDtoBuilderWithInvalidRatingOrder(): void
    {
        $result = $this->productService->requestRatingFiltersDtoBuilder('invalid_order', null, null);

        $this->assertInstanceOf(RequestRatingFiltersDto::class, $result);
        $this->assertEquals(CommentSortFilterCode::RATING_AVERAGE_DESC, $result->ratingOrder);
        $this->assertNull($result->rating);
        $this->assertNull($result->publicationOrder);
    }

    public function testRequestRatingFiltersDtoBuilderWithValidPublicationOrder(): void
    {
        $result = $this->productService->requestRatingFiltersDtoBuilder(null, null, 'POSTED_ASC');

        $this->assertInstanceOf(RequestRatingFiltersDto::class, $result);
        $this->assertEquals(CommentSortFilterCode::RATING_AVERAGE_DESC, $result->ratingOrder);
        $this->assertNull($result->rating);
        $this->assertEquals(CommentSortFilterCode::POSTED_ASC, $result->publicationOrder);
    }

    public function testRequestRatingFiltersDtoBuilderWithInvalidPublicationOrder(): void
    {
        $result = $this->productService->requestRatingFiltersDtoBuilder(null, null, 'invalid_order');

        $this->assertInstanceOf(RequestRatingFiltersDto::class, $result);
        $this->assertEquals(CommentSortFilterCode::RATING_AVERAGE_DESC, $result->ratingOrder);
        $this->assertNull($result->rating);
        $this->assertEquals(CommentSortFilterCode::POSTED_DESC, $result->publicationOrder);
    }

    public function testRequestRatingFiltersDtoBuilderWithAllParameters(): void
    {
        $result = $this->productService->requestRatingFiltersDtoBuilder('RATING_AVERAGE_DESC', 2, 'POSTED_ASC');

        $this->assertInstanceOf(RequestRatingFiltersDto::class, $result);
        $this->assertEquals(CommentSortFilterCode::RATING_AVERAGE_EQUAL, $result->ratingOrder);
        $this->assertEquals(2, $result->rating);
        $this->assertEquals(CommentSortFilterCode::POSTED_ASC, $result->publicationOrder);
    }

    // Test edge cases pour les méthodes privées (testées via les méthodes publiques)
    public function testGetAllProductsWithProductWithoutVariant(): void
    {
        $requestFiltersDto = new RequestProductFiltersDto(
            search: '',
            filter: ProductSortFilterCode::PRICE_ASC,
            productType: ProductSortFilterCode::PRODUCTS_ALL,
            categoryPublicId: null
        );

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getProductVariants')->willReturn(new ArrayCollection([]));

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$product]));

        $this->productRepository
            ->method('paginateProducts')
            ->willReturn($paginator);

        $this->productRepository
            ->method('getAverageRatingsForProducts')
            ->willReturn([]);

        $this->paginationService
            ->method('getMetaPaginationData')
            ->willReturn(new PaginationDataDto(1, 0, 0, 10, false, false));

        $result = $this->productService->getAllProducts(1, 10, $requestFiltersDto);

        // Le produit sans variant doit être ignoré
        $this->assertCount(0, $result['products']);
    }

    public function testGetAllProductsWithProductVariantWithoutImage(): void
    {
        $requestFiltersDto = new RequestProductFiltersDto(
            search: '',
            filter: ProductSortFilterCode::PRICE_ASC,
            productType: ProductSortFilterCode::PRODUCTS_ALL,
            categoryPublicId: null
        );

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getId')->willReturn(1);
        $productVariant->method('getImages')->willReturn(new ArrayCollection([]));

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getProductVariants')->willReturn(new ArrayCollection([$productVariant]));

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$product]));

        $this->productRepository
            ->method('paginateProducts')
            ->willReturn($paginator);

        $this->productRepository
            ->method('getAverageRatingsForProducts')
            ->willReturn([]);

        $this->paginationService
            ->method('getMetaPaginationData')
            ->willReturn(new PaginationDataDto(1, 0, 0, 10, false, false));

        $result = $this->productService->getAllProducts(1, 10, $requestFiltersDto);

        // Le produit avec variant sans image doit être ignoré
        $this->assertCount(0, $result['products']);
    }

    public function testGetProductReviewsWithUserWithoutReview(): void
    {
        $publicId = 'variant123';
        $requestRatingFiltersDto = new RequestRatingFiltersDto(
            ratingOrder: CommentSortFilterCode::RATING_AVERAGE_DESC,
            rating: null,
            publicationOrder: null
        );

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getId')->willReturn(1);

        $this->productVariantRepository
            ->method('findOneBy')
            ->willReturn($productVariant);

        $review = $this->createMock(ProductReview::class);
        $review->method('getUserId')->willReturn(null); // Pas d'utilisateur

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$review]));

        $this->productReviewRepository
            ->method('paginateProductReviews')
            ->willReturn($paginator);

        $this->paginationService
            ->method('getMetaPaginationData')
            ->willReturn(new PaginationDataDto(1, 0, 0, false, false, 5));

        $result = $this->productService->getProductReviewsByProductVariantPublicId($publicId, 1, 5, $requestRatingFiltersDto);

        // Les reviews sans utilisateur doivent être ignorées
        $this->assertCount(0, $result['products']);
    }

    public function testProductVariantToProductDetailDtoWithCustomMadeProduct(): void
    {
        $publicId = 'variant123';

        $category = $this->createMock(Category::class);
        $category->method('getName')->willReturn('Meubles');
        $category->method('getPublicId')->willReturn('cat123');

        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn(1);
        $product->method('getName')->willReturn('Table custom');
        $product->method('getDescription')->willReturn('Table sur mesure');
        $product->method('getCategoryId')->willReturn($category);

        $image = $this->createMock(Image::class);
        $imageCollection = new ArrayCollection([$image]);

        $wood = $this->createMock(Wood::class);
        $wood->method('getName')->willReturn('Chêne');

        $productVariant = $this->createMock(ProductVariant::class);
        $productVariant->method('getProductId')->willReturn($product);
        $productVariant->method('getPrice')->willReturn(25000);
        $productVariant->method('getStock')->willReturn(null); // Custom made
        $productVariant->method('getPublicId')->willReturn($publicId);
        $productVariant->method('getImages')->willReturn($imageCollection);
        $productVariant->method('getWoodId')->willReturn($wood);

        $this->productVariantRepository
            ->method('getProductVariantByPublicId')
            ->willReturn($productVariant);

        $this->productVariantRepository
            ->method('getAllMinimalProductVariant')
            ->willReturn([$productVariant]);

        $imageDto = new ImageDto('http://example.com/image.jpg');
        $this->imageService
            ->method('imageToImageDto')
            ->willReturn($imageDto);

        $this->imageService
            ->method('imagesToImageDtos')
            ->willReturn([$imageDto]);

        $result = $this->productService->getProductVariantByPublicId($publicId);

        $this->assertInstanceOf(ProductDetailDto::class, $result);
        $this->assertEquals(ProductType::CUSTOM_MADE, $result->shortProduct->type);
        $this->assertNull($result->stock);
    }
}
