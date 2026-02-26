<?php

namespace App\Tests\Manager\User;

use App\Entity\User\Address;
use App\Entity\User\User;
use App\Manager\User\AddressManager;
use App\Repository\User\AddressRepository;
use App\Util\ValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;

class AddressManagerTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private AddressRepository&MockObject $addressRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private ValidatorUtil $validatorUtil;
    private AddressManager $sut;

    protected function setUp(): void
    {
        $this->logger            = $this->createMock(LoggerInterface::class);
        $this->addressRepository = $this->createMock(AddressRepository::class);
        $this->entityManager     = $this->createMock(EntityManagerInterface::class);

        // Address has no validation constraints — real validator is safe to use
        $validator           = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $validatorLogger     = $this->createMock(LoggerInterface::class);
        $this->validatorUtil = new ValidatorUtil($validator, $validatorLogger);

        $this->sut = new AddressManager(
            $this->addressRepository,
            $this->entityManager,
            $this->logger,
            $this->validatorUtil,
            userMaxAddresses: 5
        );
    }

    private function buildAddress(): Address
    {
        return (new Address())
            ->setPublicId('abc123')
            ->setIsDefault(false)
            ->setIsProfessional(false)
            ->setStreet('1 rue de la Paix')
            ->setZipcode('75001')
            ->setCity('Paris');
    }

    private function buildUser(): User
    {
        return (new User())
            ->setEmail('test@example.com')
            ->setFirstname('Jean')
            ->setLastname('Dupont');
    }

    // --- canUserAddAddress ---

    public function testCanUserAddAddressReturnsTrueWhenBelowLimit(): void
    {
        $user = $this->buildUser();
        $this->addressRepository->method('count')->willReturn(3);

        $this->assertTrue($this->sut->canUserAddAddress($user));
    }

    public function testCanUserAddAddressReturnsFalseWhenAtLimit(): void
    {
        $user = $this->buildUser();
        $this->addressRepository->method('count')->willReturn(5);

        $this->assertFalse($this->sut->canUserAddAddress($user));
    }

    public function testCanUserAddAddressReturnsFalseWhenAboveLimit(): void
    {
        $user = $this->buildUser();
        $this->addressRepository->method('count')->willReturn(99);

        $this->assertFalse($this->sut->canUserAddAddress($user));
    }

    // --- countTheNumberOfAddressesForAUser ---

    public function testCountReturnsRepositoryResult(): void
    {
        $user = $this->buildUser();
        $this->addressRepository->expects($this->once())
            ->method('count')
            ->with(['userId' => $user])
            ->willReturn(2);

        $this->assertSame(2, $this->sut->countTheNumberOfAddressesForAUser($user));
    }

    // --- setADefaultAddress ---

    public function testSetADefaultAddressSetsDefaultWhenNoExistingAddress(): void
    {
        $user    = $this->buildUser();
        $address = $this->buildAddress();
        $address->setIsDefault(false);
        $this->addressRepository->method('count')->willReturn(0);

        $result = $this->sut->setADefaultAddress($address, $user);

        $this->assertTrue($result->isDefault());
    }

    public function testSetADefaultAddressKeepsDefaultFlagWhenExplicitlyRequested(): void
    {
        $user    = $this->buildUser();
        $address = $this->buildAddress();
        $address->setIsDefault(true);
        $this->addressRepository->method('count')->willReturn(3);

        $result = $this->sut->setADefaultAddress($address, $user);

        $this->assertTrue($result->isDefault());
    }

    public function testSetADefaultAddressDoesNotSetDefaultWhenNotFirstAndNotRequested(): void
    {
        $user    = $this->buildUser();
        $address = $this->buildAddress();
        $address->setIsDefault(false);
        $this->addressRepository->method('count')->willReturn(3);

        $result = $this->sut->setADefaultAddress($address, $user);

        $this->assertFalse($result->isDefault());
    }

    // --- unsetAllDefaultAddresses ---

    public function testUnsetAllDefaultAddressesDelegatesToRepository(): void
    {
        $user = $this->buildUser();
        $this->addressRepository->expects($this->once())
            ->method('unsetAllDefaultAddresses')
            ->with($user);

        $this->sut->unsetAllDefaultAddresses($user);
    }

    // --- getAddressByPublicId ---

    public function testGetAddressByPublicIdReturnsAddressWhenFound(): void
    {
        $user    = $this->buildUser();
        $address = $this->buildAddress();
        $this->addressRepository->method('findOneBy')
            ->with(['userId' => $user, 'publicId' => 'abc123'])
            ->willReturn($address);

        $result = $this->sut->getAddressByPublicId($user, 'abc123');

        $this->assertSame($address, $result);
    }

    public function testGetAddressByPublicIdReturnsNullWhenNotFound(): void
    {
        $user = $this->buildUser();
        $this->addressRepository->method('findOneBy')->willReturn(null);

        $this->assertNull($this->sut->getAddressByPublicId($user, 'unknown'));
    }

    // --- getDefaultAddressForUser ---

    public function testGetDefaultAddressForUserReturnsDefaultAddress(): void
    {
        $user    = $this->buildUser();
        $address = $this->buildAddress();
        $address->setIsDefault(true);
        $this->addressRepository->method('findOneBy')
            ->with(['userId' => $user, 'isDefault' => true])
            ->willReturn($address);

        $result = $this->sut->getDefaultAddressForUser($user);

        $this->assertSame($address, $result);
    }

    public function testGetDefaultAddressForUserReturnsNullWhenNoneFound(): void
    {
        $user = $this->buildUser();
        $this->addressRepository->method('findOneBy')->willReturn(null);

        $this->assertNull($this->sut->getDefaultAddressForUser($user));
    }

    // --- getAllAddresses ---

    public function testGetAllAddressesReturnsAddressesFromUserCollection(): void
    {
        $user    = $this->buildUser();
        $address = $this->buildAddress();
        $user->addAddress($address);

        $result = $this->sut->getAllAddresses($user);

        $this->assertSame([$address], $result);
    }

    public function testGetAllAddressesReturnsEmptyArrayWhenUserHasNone(): void
    {
        $user   = $this->buildUser();
        $result = $this->sut->getAllAddresses($user);

        $this->assertSame([], $result);
    }

    // --- create ---

    public function testCreatePersistsAndFlushesAddress(): void
    {
        $address = $this->buildAddress();

        $this->entityManager->expects($this->once())->method('persist')->with($address);
        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->create($address);
    }

    public function testCreateLogsErrorWhenFlushThrows(): void
    {
        $user    = $this->buildUser();
        $address = $this->buildAddress();
        $address->setUserId($user);
        $this->entityManager->method('persist');
        $this->entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error creating address', $this->arrayHasKey('exception'));

        $this->sut->create($address);
    }

    // --- update ---

    public function testUpdateSetsUpdatedAtPersistsAndFlushes(): void
    {
        $user    = $this->buildUser();
        $address = $this->buildAddress();
        $address->setUserId($user);
        $before = new \DateTimeImmutable('2000-01-01');
        $address->setUpdatedAt($before);

        $this->entityManager->expects($this->once())->method('persist')->with($address);
        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->update($address);

        $this->assertGreaterThan($before, $address->getUpdatedAt());
    }

    public function testUpdateLogsErrorWhenFlushThrows(): void
    {
        $user    = $this->buildUser();
        $address = $this->buildAddress();
        $address->setUserId($user);
        $this->entityManager->method('persist');
        $this->entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error updating address', $this->arrayHasKey('exception'));

        $this->sut->update($address);
    }

    // --- delete ---

    public function testDeleteCallsRemoveThenFlush(): void
    {
        $address = $this->buildAddress();

        $this->entityManager->expects($this->once())->method('remove')->with($address);
        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->delete($address);
    }

    public function testDeleteLogsErrorWhenFlushThrows(): void
    {
        $user    = $this->buildUser();
        $address = $this->buildAddress();
        $address->setUserId($user);
        $this->entityManager->method('flush')->willThrowException(new \RuntimeException('DB error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error deleting address', $this->arrayHasKey('exception'));

        $this->sut->delete($address);
    }
}
