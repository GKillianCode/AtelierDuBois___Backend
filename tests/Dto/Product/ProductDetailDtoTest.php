<?php

namespace App\Tests\Dto\Product;

use App\Dto\Product\ImageDto;
use App\Dto\Product\PriceDto;
use App\Dto\Product\PublicIdDto;
use App\Dto\Product\ShortProductDto;
use App\Dto\Product\ProductDetailDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductDetailDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidProductDetail(): void
    {
        $dto = new ProductDetailDto(
            shortProduct: new ShortProductDto(
                title: 'Table en chêne massif',
                unitPrice: new PriceDto(
                    amount: 499,
                ),
                mainImage: new ImageDto(
                    imageUrl: 'https://example.com/images/table-chene-main.webp'
                ),
                publicId: new PublicIdDto(publicId: '1A2bC3dE4fG5hI6jK7lM22'),
            ),
            stock: 10,
            description: 'Une magnifique table en bois de chêne massif avec finition naturelle.',
            imageUrls: [
                new ImageDto(
                    'https://example.com/images/table-chene-1.webp'
                ),
                new ImageDto(
                    'https://example.com/images/table-chene-2.webp'
                )
            ]
        );

        $violations = $this->validator->validate($dto);
        $this->assertCount(0, $violations);
    }
}
