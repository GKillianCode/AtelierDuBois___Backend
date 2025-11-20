<?php

namespace App\Tests\Dto\Product;

use App\Dto\Product\RequestShortProductsDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestShortProductsDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidRequestShortProductsDto(): void
    {
        $dto = new RequestShortProductsDto(
            page: 1,
            itemsPerPage: 20
        );

        $violations = $this->validator->validate($dto);
        $this->assertCount(0, $violations);
    }

    public function testInvalidPage(): void
    {
        $dto = new RequestShortProductsDto(
            page: 0,
            itemsPerPage: 20
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }

    public function testTooShortItemsPerPage(): void
    {
        $dto = new RequestShortProductsDto(
            page: 1,
            itemsPerPage: 0
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }

    public function testTooManyItemsPerPage(): void
    {
        $dto = new RequestShortProductsDto(
            page: 1,
            itemsPerPage: 200000
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }
}
