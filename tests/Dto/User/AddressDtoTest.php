<?php

namespace App\Tests\Dto\User;

use App\Dto\User\AddressDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddressDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidAddress(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: '123 Rue de la Paix',
            city: 'Paris',
            zipcode: '75001',
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertCount(0, $violations);
    }

    // Street tests

    public function testBlankStreet(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: '',
            city: 'Paris',
            zipcode: '75001',
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testTooShortStreet(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: 'A',
            city: 'Paris',
            zipcode: '75001',
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testTooLongStreet(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: str_repeat('A', 256),
            city: 'Paris',
            zipcode: '75001',
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations->count());
    }

    // City tests

    public function testBlankCity(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: '123 Rue de la Paix',
            city: '',
            zipcode: '75001',
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testTooShortCity(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: '123 Rue de la Paix',
            city: 'P',
            zipcode: '75001',
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testTooLongCity(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: '123 Rue de la Paix',
            city: str_repeat('A', 256),
            zipcode: '75001',
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations->count());
    }

    // Zipcode tests

    public function testBlankZipcode(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: '123 Rue de la Paix',
            city: 'Paris',
            zipcode: '',
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testTooLongZipcode(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: '123 Rue de la Paix',
            city: 'Paris',
            zipcode: str_repeat('A', 101),
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testSpecialZipcode(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: '123 Rue de la Paix',
            city: 'Paris',
            zipcode: '123@#!',
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testGoodSpecialZipcode(): void
    {
        $dto = new AddressDto(
            publicId: null,
            street: '123 Rue de la Paix',
            city: 'Paris',
            zipcode: 'A2',
            isProfessionnal: false,
            isDefault: true
        );

        $violations = $this->validator->validate($dto);
        $this->assertEquals(0, $violations->count());
    }
}
