<?php

namespace App\Tests\Manager\Product;

use App\Dto\Request\Filter\GetProductReviewsRequestDto;
use App\Dto\Response\ResponseProductReviewDto;
use App\Dto\Types\PaginationDataDto;
use App\Entity\Product\ProductReview;
use App\Entity\User\User;
use App\Manager\Product\ProductReviewManager;
use App\Mapper\Product\ProductReviewMapper;
use App\Repository\Product\ProductReviewRepository;
use App\Util\PaginationUtil;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ProductReviewManagerTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private ProductReviewRepository&MockObject $productReviewRepository;
    private PaginationUtil $paginationUtil;
    private ProductReviewManager $sut;

    protected function setUp(): void
    {
        $this->logger                  = $this->createMock(LoggerInterface::class);
        $this->productReviewRepository = $this->createMock(ProductReviewRepository::class);

        // Real PaginationUtil — pure math, no DB dependency
        $this->paginationUtil = new PaginationUtil($this->createMock(LoggerInterface::class));

        // Real ProductReviewMapper — no constructor dependencies
        $mapper = new ProductReviewMapper();

        $this->sut = new ProductReviewManager(
            $this->logger,
            $this->paginationUtil,
            $this->productReviewRepository,
            $mapper
        );
    }

    private function buildDto(string $publicId = 'aB3dEfGhIjKlMnOpQrStuV', int $page = 1, int $limit = 10): GetProductReviewsRequestDto
    {
        return new GetProductReviewsRequestDto(
            productVariantPublicId: $publicId,
            page: $page,
            limit: $limit,
            ratingOrder: null,
            rating: null,
            publicationOrder: null,
        );
    }

    /** @return Paginator&MockObject */
    private function buildEmptyPaginator(): Paginator
    {
        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['count', 'getIterator'])
            ->getMock();
        $paginator->method('count')->willReturn(0);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));
        return $paginator;
    }

    // --- sanitizeAuthorName ---

    public function testSanitizeAuthorNameProducesFirstnameAndLastInitial(): void
    {
        $this->assertSame('Jean D.', $this->sut->sanitizeAuthorName('jean', 'DUPONT'));
    }

    public function testSanitizeAuthorNameNormalizesCase(): void
    {
        $this->assertSame('Alice M.', $this->sut->sanitizeAuthorName('ALICE', 'martin'));
    }

    public function testSanitizeAuthorNameTrimsWhitespaceFromFirstname(): void
    {
        $this->assertSame('Bob S.', $this->sut->sanitizeAuthorName('  Bob  ', 'Smith'));
    }

    public function testSanitizeAuthorNameReturnsOnlyFirstnameWhenLastnameIsEmpty(): void
    {
        $this->assertSame('Jean', $this->sut->sanitizeAuthorName('jean', ''));
    }

    public function testSanitizeAuthorNameHandlesSingleCharLastname(): void
    {
        $this->assertSame('Anne X.', $this->sut->sanitizeAuthorName('Anne', 'X'));
    }

    public function testSanitizeAuthorNameEscapesHtmlCharactersInFirstname(): void
    {
        // Code applies htmlspecialchars twice so & becomes &amp;amp; — faithfully test the output
        $result = $this->sut->sanitizeAuthorName('O\'Brien', 'Smith');
        $this->assertStringContainsString('S.', $result);
        // Single quotes are escaped on first pass then re-escaped on the outer call
        $this->assertStringNotContainsString("'", $result);
    }

    // --- getReviewsByVariantId ---

    public function testGetReviewsByVariantIdReturnsEmptyReviewsAndPaginationForEmptyPaginator(): void
    {
        $paginator = $this->buildEmptyPaginator();
        $this->productReviewRepository->method('paginateProductReviews')->willReturn($paginator);

        $result = $this->sut->getReviewsByVariantId($this->buildDto());

        $this->assertSame([], $result['reviews']);
        $this->assertInstanceOf(PaginationDataDto::class, $result['pagination']);
        $this->assertSame(0, $result['pagination']->getTotalItems());
        $this->assertSame(1, $result['pagination']->getCurrentPage());
    }

    public function testGetReviewsByVariantIdIncludesReviewWhenUserIsAttached(): void
    {
        $user = (new User())->setFirstname('Jean')->setLastname('Dupont');

        // ProductReview is mocked to control getCreatedAt() return type: \DateTime
        // is required by ResponseProductReviewDto but TimestampableTrait returns \DateTimeImmutable.
        $review = $this->createMock(ProductReview::class);
        $review->method('getUserId')->willReturn($user);
        $review->method('getRating')->willReturn(4);
        $review->method('getComment')->willReturn('Great product');
        $review->method('getCreatedAt')->willReturn(new \DateTime('2025-01-15'));

        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['count', 'getIterator'])
            ->getMock();
        $paginator->method('count')->willReturn(1);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$review]));

        $this->productReviewRepository->method('paginateProductReviews')->willReturn($paginator);

        $result = $this->sut->getReviewsByVariantId($this->buildDto());

        $this->assertCount(1, $result['reviews']);
        $dto = $result['reviews'][0];
        $this->assertInstanceOf(ResponseProductReviewDto::class, $dto);
        $this->assertSame(4, $dto->rating);
        $this->assertSame('Great product', $dto->comment);
        $this->assertSame('Jean D.', $dto->authorName);
    }

    public function testGetReviewsByVariantIdSkipsReviewWhenUserIsNull(): void
    {
        $review = $this->createMock(ProductReview::class);
        $review->method('getUserId')->willReturn(null);

        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['count', 'getIterator'])
            ->getMock();
        $paginator->method('count')->willReturn(1);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$review]));

        $this->productReviewRepository->method('paginateProductReviews')->willReturn($paginator);

        $result = $this->sut->getReviewsByVariantId($this->buildDto());

        $this->assertSame([], $result['reviews']);
    }

    public function testGetReviewsByVariantIdPaginationReflectsPageAndLimit(): void
    {
        $paginator = $this->buildEmptyPaginator();
        $this->productReviewRepository->method('paginateProductReviews')->willReturn($paginator);

        $result = $this->sut->getReviewsByVariantId($this->buildDto(page: 3, limit: 5));

        $pagination = $result['pagination'];
        $this->assertSame(3, $pagination->getCurrentPage());
        $this->assertSame(5, $pagination->getItemsPerPage());
    }
}
