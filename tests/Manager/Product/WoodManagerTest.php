<?php

namespace App\Tests\Manager\Product;

use App\Entity\Product\Wood;
use App\Manager\Product\WoodManager;
use App\Repository\Product\WoodRepository;
use App\Util\ValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;

class WoodManagerTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private WoodRepository&MockObject $woodRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private WoodManager $sut;

    protected function setUp(): void
    {
        $this->logger         = $this->createMock(LoggerInterface::class);
        $this->woodRepository = $this->createMock(WoodRepository::class);
        $this->entityManager  = $this->createMock(EntityManagerInterface::class);

        // Real ValidatorUtil — Wood has no Assert constraints and none of the
        // tested paths call validateAndSave, so no DB interaction is triggered.
        $validatorUtil = new ValidatorUtil(
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
            $this->createMock(LoggerInterface::class)
        );

        $this->sut = new WoodManager(
            $this->logger,
            $this->woodRepository,
            $this->entityManager,
            $validatorUtil
        );
    }

    // --- update ---

    public function testUpdateSetsUpdatedAtAndFlushes(): void
    {
        $wood   = new Wood();
        $before = new \DateTimeImmutable('2000-01-01');
        $wood->setUpdatedAt($before);

        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->update($wood);

        $this->assertGreaterThan($before, $wood->getUpdatedAt());
    }

    public function testUpdateLogsErrorWhenFlushThrows(): void
    {
        $wood = new Wood();
        $this->entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error updating wood', $this->arrayHasKey('exception'));

        $this->sut->update($wood);
    }

    // --- delete ---

    public function testDeleteCallsRemoveThenFlush(): void
    {
        $wood = new Wood();

        $this->entityManager->expects($this->once())->method('remove')->with($wood);
        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->delete($wood);
    }

    public function testDeleteLogsErrorWhenFlushThrows(): void
    {
        $wood = new Wood();
        $this->entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error deleting wood', $this->arrayHasKey('exception'));

        $this->sut->delete($wood);
    }
}
