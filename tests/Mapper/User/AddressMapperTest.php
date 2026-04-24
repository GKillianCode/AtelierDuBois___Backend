<?php

namespace App\Tests\Mapper\User;

use App\Dto\Types\PublicIdDto;
use App\Dto\User\AddressDto;
use App\Entity\User\Address;
use App\Entity\User\User;
use App\Mapper\User\AddressMapper;
use App\Util\UuidUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AddressMapperTest extends TestCase
{
    private AddressMapper $mapper;
    private LoggerInterface&MockObject $logger;
    private UuidUtil $uuidUtil;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->uuidUtil = new UuidUtil($this->logger);

        $this->mapper = new AddressMapper($this->logger, $this->uuidUtil);
    }

    // -------------------------------------------------------------------------
    // toEntityFromDto
    // -------------------------------------------------------------------------

    public function testToEntityFromDtoWithPublicId(): void
    {
        $publicId = 'aB3dEfGhIjKlMnOpQrStuV';
        $user = new User();

        $addressDto = new AddressDto(
            publicId: new PublicIdDto($publicId),
            street: '12 rue de la Paix',
            city: 'Paris',
            zipcode: '75001',
            isProfessional: false,
            isDefault: true,
            companyName: null
        );

        $address = $this->mapper->toEntityFromDto($addressDto, $user);

        $this->assertInstanceOf(Address::class, $address);
        $this->assertSame($publicId, $address->getPublicId());
        $this->assertSame('12 rue de la Paix', $address->getStreet());
        $this->assertSame('Paris', $address->getCity());
        $this->assertSame('75001', $address->getZipcode());
        $this->assertFalse($address->isProfessional());
        $this->assertTrue($address->isDefault());
        $this->assertSame($user, $address->getUserId());
    }

    public function testToEntityFromDtoWithoutPublicIdGeneratesUuid(): void
    {
        $user = new User();

        $addressDto = new AddressDto(
            publicId: null,
            street: '5 avenue Victor Hugo',
            city: 'Lyon',
            zipcode: '69001',
            isProfessional: true,
            isDefault: false,
            companyName: null
        );

        $address = $this->mapper->toEntityFromDto($addressDto, $user);

        $this->assertNotNull($address->getPublicId());
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]{22}$/', $address->getPublicId());
        $this->assertSame('5 avenue Victor Hugo', $address->getStreet());
        $this->assertSame('Lyon', $address->getCity());
        $this->assertSame('69001', $address->getZipcode());
        $this->assertTrue($address->isProfessional());
        $this->assertFalse($address->isDefault());
        $this->assertSame($user, $address->getUserId());
    }

    public function testToEntityFromDtoProfessionalAndDefault(): void
    {
        $user = new User();

        $addressDto = new AddressDto(
            publicId: null,
            street: '1 place Bellecour',
            city: 'Lyon',
            zipcode: '69002',
            isProfessional: true,
            isDefault: true,
            companyName: null
        );

        $address = $this->mapper->toEntityFromDto($addressDto, $user);

        $this->assertTrue($address->isProfessional());
        $this->assertTrue($address->isDefault());
    }

    // -------------------------------------------------------------------------
    // toDtoFromEntity
    // -------------------------------------------------------------------------

    public function testToDtoFromEntity(): void
    {
        $address = new Address();
        $address->setPublicId('aB3dEfGhIjKlMnOpQrStuV')
            ->setStreet('12 rue de la Paix')
            ->setCity('Paris')
            ->setZipcode('75001')
            ->setIsProfessional(false)
            ->setIsDefault(true);

        $dto = $this->mapper->toDtoFromEntity($address);

        $this->assertInstanceOf(AddressDto::class, $dto);
        $this->assertInstanceOf(PublicIdDto::class, $dto->getPublicId());
        $this->assertSame('aB3dEfGhIjKlMnOpQrStuV', $dto->getPublicId()->getPublicId());
        $this->assertSame('12 rue de la Paix', $dto->getStreet());
        $this->assertSame('Paris', $dto->getCity());
        $this->assertSame('75001', $dto->getZipcode());
        $this->assertFalse($dto->isProfessional());
        $this->assertTrue($dto->isDefault());
    }

    public function testToDtoFromEntityProfessionalAddress(): void
    {
        $address = new Address();
        $address->setPublicId('Zz9yY8xX7wW6vV5uU4tT3s')
            ->setStreet('10 boulevard Haussmann')
            ->setCity('Paris')
            ->setZipcode('75009')
            ->setIsProfessional(true)
            ->setIsDefault(false);

        $dto = $this->mapper->toDtoFromEntity($address);

        $this->assertSame('Zz9yY8xX7wW6vV5uU4tT3s', $dto->getPublicId()->getPublicId());
        $this->assertSame('10 boulevard Haussmann', $dto->getStreet());
        $this->assertSame('Paris', $dto->getCity());
        $this->assertSame('75009', $dto->getZipcode());
        $this->assertTrue($dto->isProfessional());
        $this->assertFalse($dto->isDefault());
    }

    public function testToEntityFromDtoProfessionalAddressMapsCompanyName(): void
    {
        $user = new User();

        $addressDto = new AddressDto(
            publicId: null,
            street: '10 boulevard Haussmann',
            city: 'Paris',
            zipcode: '75009',
            isProfessional: true,
            isDefault: false,
            companyName: 'Atelier Du Bois SARL'
        );

        $address = $this->mapper->toEntityFromDto($addressDto, $user);

        $this->assertSame('Atelier Du Bois SARL', $address->getCompanyName());
        $this->assertTrue($address->isProfessional());
    }

    public function testToEntityFromDtoNonProfessionalAddressHasNullCompanyName(): void
    {
        $user = new User();

        $addressDto = new AddressDto(
            publicId: null,
            street: '12 rue de la Paix',
            city: 'Paris',
            zipcode: '75001',
            isProfessional: false,
            isDefault: false,
            companyName: null
        );

        $address = $this->mapper->toEntityFromDto($addressDto, $user);

        $this->assertNull($address->getCompanyName());
    }

    public function testToDtoFromEntityWithCompanyName(): void
    {
        $address = new Address();
        $address->setPublicId('aB3dEfGhIjKlMnOpQrStuV')
            ->setStreet('10 boulevard Haussmann')
            ->setCity('Paris')
            ->setZipcode('75009')
            ->setIsProfessional(true)
            ->setIsDefault(false)
            ->setCompanyName('Atelier Du Bois SARL');

        $dto = $this->mapper->toDtoFromEntity($address);

        $this->assertSame('Atelier Du Bois SARL', $dto->getCompanyName());
        $this->assertTrue($dto->isProfessional());
    }
}
