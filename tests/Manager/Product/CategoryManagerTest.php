<?php

namespace App\Tests\Manager\Product;

use App\Entity\Product\Category;
use App\Manager\Product\CategoryManager;
use App\Repository\Product\CategoryRepository;
use App\Util\ValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;

class CategoryManagerTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private CategoryRepository&MockObject $categoryRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private CategoryManager $sut;

    protected function setUp(): void
    {
        $this->logger             = $this->createMock(LoggerInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->entityManager      = $this->createMock(EntityManagerInterface::class);

        // Real ValidatorUtil — Category has no Assert constraints and none of the
        // tested paths call validateAndSave, so no DB interaction is triggered.
        $validatorUtil = new ValidatorUtil(
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
            $this->createMock(LoggerInterface::class)
        );

        $this->sut = new CategoryManager(
            $this->logger,
            $this->categoryRepository,
            $this->entityManager,
            $validatorUtil
        );
    }

    // --- getAllCategories ---

    public function testGetAllCategoriesReturnsCategoryArray(): void
    {
        $category = new Category();
        $this->categoryRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$category]);

        $result = $this->sut->getAllCategories();

        $this->assertSame([$category], $result);
    }

    public function testGetAllCategoriesReturnsEmptyArrayWhenNoneExist(): void
    {
        $this->categoryRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->sut->getAllCategories();

        $this->assertSame([], $result);
    }

    // --- update ---

    public function testUpdateSetsUpdatedAtAndFlushes(): void
    {
        $category = new Category();
        $before   = new \DateTimeImmutable('2000-01-01');
        $category->setUpdatedAt($before);

        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->update($category);

        $this->assertGreaterThan($before, $category->getUpdatedAt());
    }

    public function testUpdateLogsErrorWhenFlushThrows(): void
    {
        $category = new Category();
        $this->entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error updating category', $this->arrayHasKey('exception'));

        $this->sut->update($category);
    }

    // --- delete ---

    public function testDeleteCallsRemoveThenFlush(): void
    {
        $category = new Category();

        $this->entityManager->expects($this->once())->method('remove')->with($category);
        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->delete($category);
    }

    public function testDeleteLogsErrorWhenFlushThrows(): void
    {
        $category = new Category();
        $this->entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error deleting category', $this->arrayHasKey('exception'));

        $this->sut->delete($category);
    }
}
