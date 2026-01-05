<?php

namespace App\Tests\Dto\Types;

use App\Dto\Types\PublicIdDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PublicIdDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidPublicId(): void
    {
        $dto = new PublicIdDto(publicId: '1A2bC3dE4fG5hI6jK7lM22');

        $violations = $this->validator->validate($dto);
        $this->assertCount(0, $violations);
    }

    public function testInvalidPublicId(): void
    {
        $dto = new PublicIdDto(publicId: '22');

        $violations = $this->validator->validate($dto);
        $this->assertGreaterThan(0, $violations);
    }
}
