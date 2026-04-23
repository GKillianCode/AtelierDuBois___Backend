<?php

namespace App\Tests\Utils;

use App\Util\PaginationUtil;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PaginationUtilTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private PaginationUtil $sut;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut    = new PaginationUtil($this->logger);
    }

    /** @return Paginator<mixed>&MockObject */
    private function paginatorWithCount(int $count): Paginator&MockObject
    {
        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paginator->method('count')->willReturn($count);
        return $paginator;
    }

    // =========================================================================
    // currentPage / totalItems / itemsPerPage passthrough
    // =========================================================================

    public function testCurrentPageIsPreserved(): void
    {
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(50), limit: 10, page: 3);

        $this->assertSame(3, $dto->getCurrentPage());
    }

    public function testTotalItemsMatchesPaginatorCount(): void
    {
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(73), limit: 20, page: 1);

        $this->assertSame(73, $dto->getTotalItems());
    }

    public function testItemsPerPageIsPreserved(): void
    {
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(50), limit: 15, page: 1);

        $this->assertSame(15, $dto->getItemsPerPage());
    }

    // =========================================================================
    // totalPages calculation
    // =========================================================================

    public function testTotalPagesIsExactDivision(): void
    {
        // 100 / 20 = exactly 5 pages
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(100), limit: 20, page: 1);

        $this->assertSame(5, $dto->getTotalPages());
    }

    public function testTotalPagesIsCeiledWhenRemainder(): void
    {
        // 21 / 20 = 1.05 → ceil → 2 pages
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(21), limit: 20, page: 1);

        $this->assertSame(2, $dto->getTotalPages());
    }

    public function testTotalPagesIsOneWhenItemsFitInSinglePage(): void
    {
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(5), limit: 20, page: 1);

        $this->assertSame(1, $dto->getTotalPages());
    }

    public function testTotalPagesIsZeroWhenEmpty(): void
    {
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(0), limit: 20, page: 1);

        $this->assertSame(0, $dto->getTotalPages());
    }

    // =========================================================================
    // hasNextPage
    // =========================================================================

    public function testHasNextPageWhenNotOnLastPage(): void
    {
        // 3 pages total, currently on page 2
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(60), limit: 20, page: 2);

        $this->assertTrue($dto->hasNextPage());
    }

    public function testHasNoNextPageWhenOnLastPage(): void
    {
        // 3 pages total, currently on page 3
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(60), limit: 20, page: 3);

        $this->assertFalse($dto->hasNextPage());
    }

    public function testHasNoNextPageWhenSinglePage(): void
    {
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(5), limit: 20, page: 1);

        $this->assertFalse($dto->hasNextPage());
    }

    // =========================================================================
    // hasPreviousPage
    // =========================================================================

    public function testHasNoPreviousPageOnFirstPage(): void
    {
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(60), limit: 20, page: 1);

        $this->assertFalse($dto->hasPreviousPage());
    }

    public function testHasPreviousPageOnSecondPage(): void
    {
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(60), limit: 20, page: 2);

        $this->assertTrue($dto->hasPreviousPage());
    }

    public function testHasPreviousPageOnLastPage(): void
    {
        $dto = $this->sut->getMetaPaginationData($this->paginatorWithCount(60), limit: 20, page: 3);

        $this->assertTrue($dto->hasPreviousPage());
    }
}
