<?php

namespace App\Tests\Dto\User;

use App\Dto\User\RegisterUserDto;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterUserDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidRegistration(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont',
            email: 'jean.dupont@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertCount(0, $violations);
    }

    // Firstname tests

    public function testFirstnameBlank(): void
    {
        $dto = new RegisterUserDto(
            firstname: '',
            lastname: 'Dupont',
            email: 'jean@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testFirstnameTooShort(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'J',
            lastname: 'Dupont',
            email: 'jean@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testFirstnameTooLong(): void
    {
        $dto = new RegisterUserDto(
            firstname: str_repeat('A', 51),
            lastname: 'Dupont',
            email: 'jean@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testFirstnameInvalidCharacters(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean123',
            lastname: 'Dupont',
            email: 'jean@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testFirstnameWithAccents(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Loïs',
            lastname: 'Muller',
            email: 'test@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertCount(0, $violations);
    }

    public function testFirstnameWithHyphen(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean-Pierre',
            lastname: 'Dupont',
            email: 'test@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertCount(0, $violations);
    }

    public function testFirstnameWithSpace(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean Marie',
            lastname: 'Dupont',
            email: 'test@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertCount(0, $violations);
    }

    // Lastname tests

    public function testLastnameBlank(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: '',
            email: 'jean@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testLastnameTooShort(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: "a",
            email: 'jean@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testLastnameTooLong(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: str_repeat('A', 51),
            email: 'jean@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testLastnameInvalidCharacters(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont123',
            email: 'jean@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testLastnameWithAccents(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'François',
            lastname: 'Müller',
            email: 'test@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertCount(0, $violations);
    }

    public function testLastnameWithHyphen(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont-Pierre',
            email: 'test@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertCount(0, $violations);
    }

    public function testLastnameWithSpace(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont Pierre',
            email: 'test@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertCount(0, $violations);
    }

    // Email tests

    public function testEmailBlank(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont',
            email: '',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testEmailInvalid(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont',
            email: 'invalid-email',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testEmailTooShort(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont',
            email: str_repeat('a', 1) . '@e.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testEmailTooLong(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont',
            email: str_repeat('a', 140) . '@example.com',
            password: 'SecurePass123!'
        );

        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    // Password tests

    public function testBlankPassword(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont',
            email: 'jean@example.com',
            password: ''
        );
        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testPasswordWeak(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont',
            email: 'jean@example.com',
            password: '12345'
        );
        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testPasswordStrong(): void
    {
        $dto = new RegisterUserDto(
            firstname: 'Jean',
            lastname: 'Dupont',
            email: 'jean@example.com',
            password: 'Abricot!234@'
        );
        $violations = $this->validator->validate($dto, null, ['registration']);
        $this->assertCount(0, $violations);
    }
}
