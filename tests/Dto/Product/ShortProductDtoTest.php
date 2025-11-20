<?php

namespace App\Tests\Dto\Product;

use App\Dto\Product\ImageDto;
use App\Dto\Product\PriceDto;
use App\Dto\Product\PublicIdDto;
use App\Dto\Product\ShortProductDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShortProductDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidShortProductDto(): void
    {
        $dto = new ShortProductDto(
            title: 'Table en bois',
            unitPrice: new PriceDto(amount: 19999),
            mainImage: new ImageDto(imageUrl: 'https://example.com/image.webp'),
            publicId: new PublicIdDto(publicId: '1A2bC3dE4fG5hI6jK7lM22')
        );

        $violations = $this->validator->validate($dto);
        $this->assertCount(0, $violations);
    }

    public function testTooShortTitle(): void
    {
        $dto = new ShortProductDto(
            title: 'Tb',
            unitPrice: new PriceDto(amount: 19999),
            mainImage: new ImageDto(imageUrl: 'https://example.com/image.webp'),
            publicId: new PublicIdDto(publicId: '1A2bC3dE4fG5hI6jK7lM22')
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }

    public function testTooLongTitle(): void
    {
        $dto = new ShortProductDto(
            title: 'T' . str_repeat('a', 300),
            unitPrice: new PriceDto(amount: 19999),
            mainImage: new ImageDto(imageUrl: 'https://example.com/image.webp'),
            publicId: new PublicIdDto(publicId: '1A2bC3dE4fG5hI6jK7lM22')
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }
}
