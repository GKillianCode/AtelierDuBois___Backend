<?php

namespace App\Tests\Dto\Product;

use App\Dto\Types\CategoryDto;
use App\Enum\ProductType;
use App\Dto\Types\ImageDto;
use App\Dto\Types\PriceDto;
use App\Dto\Types\PublicIdDto;
use App\Dto\Product\ShortProductDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShortProductDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;
    private CategoryDto $category;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);

        $this->category = new CategoryDto(
            name: 'Meubles',
            publicId: new PublicIdDto(publicId: 'a1B2c3D4e5F6g7H8i9J0k11'),
        );
    }

    public function testValidShortProductDto(): void
    {
        $dto = new ShortProductDto(
            id: 1,
            title: 'Table en bois',
            type: ProductType::IN_STOCK,
            category: $this->category,
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
            id: 2,
            title: 'Tb',
            type: ProductType::IN_STOCK,
            category: $this->category,
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
            id: 3,
            title: 'T' . str_repeat('a', 300),
            type: ProductType::IN_STOCK,
            category: $this->category,
            unitPrice: new PriceDto(amount: 19999),
            mainImage: new ImageDto(imageUrl: 'https://example.com/image.webp'),
            publicId: new PublicIdDto(publicId: '1A2bC3dE4fG5hI6jK7lM22')
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }
}
