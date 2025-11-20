<?php

namespace App\Tests\Dto\Product;

use App\Dto\Product\ImageDto;
use App\Dto\Product\WoodTypeDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WoodTypeDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidWoodType(): void
    {
        $dto = new WoodTypeDto(
            name: 'CHENE',
            mainImageUrl: new ImageDto(
                'https://example.com/images/chene.webp'
            )
        );

        $violations = $this->validator->validate($dto);
        $this->assertCount(0, $violations);
    }

    // name tests

    public function testBlankName(): void
    {
        $dto = new WoodTypeDto(
            name: '',
            mainImageUrl: new ImageDto(
                'https://example.com/images/chene.webp'
            )
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }

    public function testTooShortName(): void
    {
        $dto = new WoodTypeDto(
            name: 'A',
            mainImageUrl: new ImageDto(
                'https://example.com/images/chene.webp'
            )
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }

    public function testTooLongName(): void
    {
        $dto = new WoodTypeDto(
            name: str_repeat('A', 101),
            mainImageUrl: new ImageDto(
                'https://example.com/images/chene.webp'
            )
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }

    public function testSpecialCharsName(): void
    {
        $dto = new WoodTypeDto(
            name: 'Chêne@!',
            mainImageUrl: new ImageDto(
                'https://example.com/images/chene.webp'
            )
        );

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }
}
