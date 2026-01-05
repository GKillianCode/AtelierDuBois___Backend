<?php

namespace App\Tests\Service\Product;

use App\Entity\User\User;
use App\Dto\User\AddressDto;
use App\Dto\Types\PublicIdDto;
use App\Entity\User\Address;
use App\Service\UuidService;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use App\Service\ValidatorService;
use App\Service\User\AddressService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\User\AddressRepository;
use PHPUnit\Framework\MockObject\MockObject;

class AddressServiceTest extends TestCase
{
    private AddressService $addressService;
    private MockObject $logger;
    private MockObject $entityManager;
    private MockObject $uuidService;
    private MockObject $addressRepository;
    private MockObject $validatorService;
    private User $user;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->uuidService = $this->createMock(UuidService::class);
        $this->addressRepository = $this->createMock(AddressRepository::class);
        $this->validatorService = $this->createMock(ValidatorService::class);

        $this->addressService = new AddressService(
            $this->logger,
            $this->entityManager,
            $this->uuidService,
            $this->addressRepository,
            $this->validatorService
        );

        $this->user = new User();
    }

    // Tests pour countUserAddresses
    public function testCountUserAddresses(): void
    {
        $this->addressRepository
            ->expects($this->once())
            ->method('count')
            ->with(['userId' => $this->user])
            ->willReturn(3);

        $result = $this->addressService->countUserAddresses($this->user);

        $this->assertEquals(3, $result);
    }

    // Tests pour canAddAddress
    public function testCanAddAddressWhenUnderLimit(): void
    {
        $this->addressRepository
            ->method('count')
            ->willReturn(3);

        $result = $this->addressService->canAddAddress($this->user);

        $this->assertTrue($result);
    }

    public function testCanAddAddressWhenAtLimit(): void
    {
        $this->addressRepository
            ->method('count')
            ->willReturn(5);

        $result = $this->addressService->canAddAddress($this->user);

        $this->assertFalse($result);
    }

    public function testCanAddAddressWhenOverLimit(): void
    {
        $this->addressRepository
            ->method('count')
            ->willReturn(6);

        $result = $this->addressService->canAddAddress($this->user);

        $this->assertFalse($result);
    }

    // Tests pour addAddress
    public function testAddAddressFirstAddressSetAsDefault(): void
    {
        $addressDto = new AddressDto(
            publicId: null,
            street: '123 Rue Test',
            city: 'Paris',
            zipcode: '75001',
            isProfessionnal: false,
            isDefault: false
        );

        $this->addressRepository
            ->method('count')
            ->willReturn(0);

        $this->uuidService
            ->method('generateUuid62')
            ->willReturn('537u4s94BWogtNsb8vKCom');

        $this->validatorService
            ->method('getViolationsAsArray')
            ->willReturn([]);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->addressService->addAddress($addressDto, $this->user);
    }

    public function testAddAddressWithValidationErrors(): void
    {
        $addressDto = new AddressDto(
            publicId: null,
            street: '',
            city: 'Paris',
            zipcode: '75001',
            isProfessionnal: false,
            isDefault: false
        );

        $this->addressRepository
            ->method('count')
            ->willReturn(0);

        $this->uuidService
            ->method('generateUuid62')
            ->willReturn('537u4s94BWogtNsb8vKCo1');

        $this->validatorService
            ->method('getViolationsAsArray')
            ->willReturn([
                ['property' => 'street', 'message' => 'Street is required']
            ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Validation error while adding address');

        $this->addressService->addAddress($addressDto, $this->user);
    }

    // Tests pour getAddressByPublicId
    public function testGetAddressByPublicIdFound(): void
    {
        $address = new Address();
        $publicId = '537u4s94BWogtNsb8vKC12';

        $this->addressRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'userId' => $this->user,
                'publicId' => $publicId
            ])
            ->willReturn($address);

        $result = $this->addressService->getAddressByPublicId($this->user, $publicId);

        $this->assertSame($address, $result);
    }

    public function testGetAddressByPublicIdNotFound(): void
    {
        $publicId = 'non-existent-id';

        $this->addressRepository
            ->method('findOneBy')
            ->willReturn(null);

        $result = $this->addressService->getAddressByPublicId($this->user, $publicId);

        $this->assertNull($result);
    }

    // Tests pour getAllAddresses
    public function testGetAllAddresses(): void
    {
        $address1 = new Address();
        $address2 = new Address();

        $this->user->addAddress($address1);
        $this->user->addAddress($address2);

        $result = $this->addressService->getAllAddresses($this->user);

        $this->assertCount(2, $result);
        $this->assertContains($address1, $result);
        $this->assertContains($address2, $result);
    }

    // Tests pour getAllAddressesInDto
    public function testGetAllAddressesInDto(): void
    {
        $address = new Address();
        $address
            ->setPublicId('537u4s94BWogtNsb8vKCdf')
            ->setStreet('123 Test Street')
            ->setCity('Test City')
            ->setZipcode('12345')
            ->setIsProfessionnal(false)
            ->setIsDefault(true);

        $this->user->addAddress($address);

        $result = $this->addressService->getAllAddressesInDto($this->user);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(AddressDto::class, $result[0]);
        $this->assertEquals('123 Test Street', $result[0]->street);
    }

    // Tests pour updateAddress
    public function testUpdateAddress(): void
    {
        $address = new Address();
        $address
            ->setPublicId('537u4s94BWogtNsb8vKCdf')
            ->setStreet('Old Street')
            ->setCity('Old City')
            ->setZipcode('00000')
            ->setIsProfessionnal(false)
            ->setIsDefault(false);

        $updateDto = new AddressDto(
            publicId: new PublicIdDto('537u4s94BWogtNsb8vKCdf'),
            street: 'New Street',
            city: 'New City',
            zipcode: '11111',
            isProfessionnal: true,
            isDefault: false
        );

        $this->addressRepository
            ->method('count')
            ->willReturn(1);

        $this->validatorService
            ->method('getViolationsAsArray')
            ->willReturn([]);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->addressService->updateAddress($address, $updateDto, $this->user);

        $this->assertEquals('New Street', $address->getStreet());
        $this->assertEquals('New City', $address->getCity());
        $this->assertEquals('11111', $address->getZipcode());
        $this->assertTrue($address->isProfessionnal());
    }

    // Tests pour removeAddressByPublicId
    public function testRemoveAddressByPublicId(): void
    {
        $address = new Address();

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($address);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->addressService->removeAddressByPublicId($address);
    }

    // Tests pour createAddressDtoFromAddress
    public function testCreateAddressDtoFromAddress(): void
    {
        $address = new Address();
        $address->setPublicId('test-public-id')
            ->setStreet('123 Test Street')
            ->setCity('Test City')
            ->setZipcode('12345')
            ->setIsProfessionnal(true)
            ->setIsDefault(false);

        $result = $this->addressService->createAddressDtoFromAddress($address);

        $this->assertInstanceOf(AddressDto::class, $result);
        $this->assertEquals('123 Test Street', $result->street);
        $this->assertEquals('Test City', $result->city);
        $this->assertEquals('12345', $result->zipcode);
        $this->assertTrue($result->isProfessionnal);
        $this->assertFalse($result->isDefault);
    }

    // Tests pour getAddressInDtoByPublicId
    public function testGetAddressInDtoByPublicIdFound(): void
    {
        $address = new Address();
        $address->setPublicId('test-id')
            ->setStreet('Test Street')
            ->setCity('Test City')
            ->setZipcode('12345')
            ->setIsProfessionnal(false)
            ->setIsDefault(true);

        $this->addressRepository
            ->method('findOneBy')
            ->willReturn($address);

        $result = $this->addressService->getAddressInDtoByPublicId($this->user, 'test-id');

        $this->assertInstanceOf(AddressDto::class, $result);
        $this->assertEquals('Test Street', $result->street);
    }

    public function testGetAddressInDtoByPublicIdNotFound(): void
    {
        $this->addressRepository
            ->method('findOneBy')
            ->willReturn(null);

        $result = $this->addressService->getAddressInDtoByPublicId($this->user, 'non-existent');

        $this->assertNull($result);
    }
}
