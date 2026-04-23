<?php

namespace App\Tests\Manager\User;

use App\Dto\Request\RegisterUserDto;
use App\Entity\User\User;
use App\Manager\User\UserManager;
use App\Repository\User\UserRepository;
use App\Util\UuidUtil;
use App\Util\ValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserManagerTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private EntityManagerInterface&MockObject $entityManager;
    private UserRepository&MockObject $userRepository;
    private ValidatorUtil&MockObject $validatorUtil;
    private UserManager $sut;

    protected function setUp(): void
    {
        $this->logger         = $this->createMock(LoggerInterface::class);
        $this->entityManager  = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->validatorUtil  = $this->createMock(ValidatorUtil::class);

        // Real UuidUtil: generates deterministic UUID v4 and base62 public IDs
        $uuidLogger = $this->createMock(LoggerInterface::class);
        $uuidUtil   = new UuidUtil($uuidLogger);

        $this->sut = new UserManager(
            $this->logger,
            $this->entityManager,
            $this->userRepository,
            $this->validatorUtil,
            $uuidUtil
        );
    }

    private function buildDto(): RegisterUserDto
    {
        return new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont',
            email: 'jean.dupont@example.com',
            password: 'Abricot2024!',
        );
    }

    private function buildUser(): User
    {
        return (new User())
            ->setEmail('jean.dupont@example.com')
            ->setFirstname('Jean')
            ->setLastname('Dupont');
    }

    // --- emailExists ---

    public function testEmailExistsReturnsTrueWhenUserFound(): void
    {
        $this->userRepository->method('findOneBy')
            ->with(['email' => 'jean.dupont@example.com'])
            ->willReturn($this->buildUser());

        $this->assertTrue($this->sut->emailExists('jean.dupont@example.com'));
    }

    public function testEmailExistsReturnsFalseWhenUserNotFound(): void
    {
        $this->userRepository->method('findOneBy')->willReturn(null);

        $this->assertFalse($this->sut->emailExists('unknown@example.com'));
    }

    // --- create ---

    public function testCreatePersistsAndFlushesWhenValidationPasses(): void
    {
        // No violations — validateAndSave proceeds to persist+flush
        $this->validatorUtil->method('getViolationsAsArray')->willReturn([]);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');
        $this->logger->expects($this->never())->method('error');

        $this->sut->create($this->buildDto());
    }

    public function testCreateLogsErrorAndDoesNotPersistWhenValidationFails(): void
    {
        // Violations returned — validateAndSave throws → create() catches and logs
        $this->validatorUtil->method('getViolationsAsArray')
            ->willReturn([['property' => 'email', 'message' => 'Invalid']]);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');
        // ValidateAndSaveTrait logs once, UserManager catches and logs again
        $this->logger->expects($this->atLeastOnce())->method('error');

        $this->sut->create($this->buildDto());
    }

    // --- update ---

    public function testUpdateSetsUpdatedAtPersistsAndFlushes(): void
    {
        $user   = $this->buildUser();
        $before = new \DateTimeImmutable('2000-01-01');
        $user->setUpdatedAt($before);
        $this->validatorUtil->method('getViolationsAsArray')->willReturn([]);

        $this->entityManager->expects($this->once())->method('persist')->with($user);
        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->update($user);

        $this->assertGreaterThan($before, $user->getUpdatedAt());
    }

    public function testUpdateLogsErrorWhenValidationFails(): void
    {
        $user = $this->buildUser();
        $this->validatorUtil->method('getViolationsAsArray')
            ->willReturn([['property' => 'email', 'message' => 'Invalid']]);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');
        // ValidateAndSaveTrait logs once, UserManager catches and logs again
        $this->logger->expects($this->atLeastOnce())->method('error');

        $this->sut->update($user);
    }

    // --- delete ---

    public function testDeleteCallsRemoveThenFlush(): void
    {
        $user = $this->buildUser();

        $this->entityManager->expects($this->once())->method('remove')->with($user);
        $this->entityManager->expects($this->once())->method('flush');
        $this->logger->expects($this->never())->method('error');

        $this->sut->delete($user);
    }

    public function testDeleteLogsErrorWhenFlushThrows(): void
    {
        $user = $this->buildUser();
        $this->entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error deleting user', $this->arrayHasKey('exception'));

        $this->sut->delete($user);
    }
}
