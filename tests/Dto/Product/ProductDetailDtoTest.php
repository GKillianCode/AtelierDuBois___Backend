<?php

namespace App\Tests\Dto\Product;

use App\Enum\ProductType;
use App\Dto\Types\ImageDto;
use App\Dto\Types\PriceDto;
use App\Dto\Types\CategoryDto;
use App\Dto\Types\PublicIdDto;
use App\Dto\Product\ShortProductDto;
use App\Dto\Product\ProductDetailDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductDetailDtoTest extends KernelTestCase
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

    public function testValidProductDetail(): void
    {
        $dto = new ProductDetailDto(
            shortProduct: new ShortProductDto(
                id: 1,
                title: 'Table en chêne massif',
                type: ProductType::IN_STOCK,
                category: $this->category,
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
            ],
            otherProductVariants: []
        );

        $violations = $this->validator->validate($dto);
        $this->assertCount(0, $violations);
    }
}
