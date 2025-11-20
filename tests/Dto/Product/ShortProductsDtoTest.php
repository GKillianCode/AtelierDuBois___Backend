<?php

namespace App\Tests\Dto\Product;

use App\Dto\Product\ShortProductsDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShortProductsDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidShortProductsDto(): void
    {
        $dto = new ShortProductsDto(
            page: 1,
            isFirstPage: true,
            isLastPage: false,
            nbPages: 5,
            products: []
        );

        $violations = $this->validator->validate($dto);
        $this->assertCount(0, $violations);
    }

    public function testNegativeCurrentPage(): void
    {
        $dto = new ShortProductsDto(
            page: -1,
            isFirstPage: true,
            isLastPage: false,
            nbPages: 5,
            products: []
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }

    public function testNegativePageNumber(): void
    {
        $dto = new ShortProductsDto(
            page: 1,
            isFirstPage: true,
            isLastPage: false,
            nbPages: -5,
            products: []
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }
}
