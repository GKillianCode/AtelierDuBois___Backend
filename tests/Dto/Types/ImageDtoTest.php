<?php

namespace App\Tests\Dto\Types;

use App\Dto\Types\ImageDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImageDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidUrl(): void
    {
        $dto = new ImageDto(
            imageUrl: 'https://example.com/image.webp'
        );

        $violations = $this->validator->validate($dto);
        $this->assertCount(0, $violations);
    }
    public function testInvalidUrl(): void
    {
        $dto = new ImageDto(
            imageUrl: 'invalid-url'
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }

    public function testTooLongUrl(): void
    {
        $dto = new ImageDto(
            imageUrl: 'https://' . str_repeat('a', 250) . '.com/image.webp'
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }
}
