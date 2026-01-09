<?php

namespace App\Tests\Dto\Order;

use App\Dto\Types\PriceDto;
use App\Dto\Types\PublicIdDto;
use App\Dto\Order\OrderProductDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderProductDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidDto(): void
    {
        $dto = new OrderProductDto(
            unitPrice: new PriceDto(amount: 5000),
            quantity: 2,
            productName: 'Chaise en bois',
            publicId: new PublicIdDto(publicId: 'prod-12345'),
            woodtype: 'Chêne'
        );

        $violations = $this->validator->validate($dto);
        $this->assertCount(0, $violations);
    }

    public function testNegativeQuantity(): void
    {
        $dto = new OrderProductDto(
            unitPrice: new PriceDto(amount: 5000),
            quantity: -2,
            productName: 'Chaise en bois',
            publicId: new PublicIdDto(publicId: 'prod-12345'),
            woodtype: 'Chêne'
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }

    public function testTooShortProductName(): void
    {
        $dto = new OrderProductDto(
            unitPrice: new PriceDto(amount: 5000),
            quantity: 2,
            productName: 'C',
            publicId: new PublicIdDto(publicId: 'prod-12345'),
            woodtype: 'Chêne'
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }

    public function testTooLongProductName(): void
    {
        $dto = new OrderProductDto(
            unitPrice: new PriceDto(amount: 5000),
            quantity: 2,
            productName: str_repeat('A', 151),
            publicId: new PublicIdDto(publicId: 'prod-12345'),
            woodtype: 'Chêne'
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }
}
