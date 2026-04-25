<?php

namespace App\Mapper\Request;

use App\Dto\Request\RegisterUserDto;
use App\Validator\Constraints\Password\StrongPassword;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterUserRequestMapper
{
    public function __construct(
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
    ) {}

    public function registerUserRequest(Request $request): RegisterUserDto
    {
        $userData = $this->extractJsonData($request);
        $this->validateParameters($userData);

        return $this->createUserDto($userData);
    }

    /** @return array<string, mixed> */
    private function extractJsonData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException($this->translator->trans('error.invalid_json', ['%detail%' => json_last_error_msg()], 'validators'));
        }


        $userData = [
            'firstname' => $data['firstname'] ?? null,
            'lastname' => $data['lastname'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
            'confirmPassword' => $data['confirmPassword'] ?? null,
        ];

        return $userData;
    }

    /** @param array<string, mixed> $data */
    private function validateParameters(array $data): void
    {
        $constraints = new Assert\Collection([
            'firstname' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length(
                    min: 2,
                    max: 50,
                    minMessage: 'user.firstname.min_length',
                    maxMessage: 'user.firstname.max_length',
                ),
                new Assert\Regex(
                    pattern: '/^[a-zA-ZÀ-ÿ\-\s]+$/',
                    message: 'user.firstname.regex',
                ),
            ],
            'lastname' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length(
                    min: 2,
                    max: 50,
                    minMessage: 'register.lastname.min_length',
                    maxMessage: 'register.lastname.max_length',
                ),
                new Assert\Regex(
                    pattern: '/^[a-zA-ZÀ-ÿ\-\s]+$/',
                    message: 'register.lastname.regex',
                ),
            ],
            'email' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Email(
                    message: 'user.email.invalid',
                ),
                new Assert\Length(
                    min: 8,
                    max: 150,
                    minMessage: 'register.email.min_length',
                    maxMessage: 'user.email.max_length',
                ),
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new StrongPassword(),
            ],
            'confirmPassword' => [
                new Assert\NotBlank(message: 'register.confirm_password.not_blank'),
                new Assert\Type('string'),
                new StrongPassword(),
                new Assert\EqualTo(
                    value: $data['password'] ?? null,
                    message: 'register.passwords_not_match',
                ),
            ],
        ]);

        $violations = $this->validator->validate($data, $constraints);

        if (\count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new BadRequestException($this->translator->trans('error.validation_failed', ['%details%' => implode(', ', $errors)], 'validators'));
        }
    }

    /** @param array<string, mixed> $userData */
    private function createUserDto(array $userData): RegisterUserDto
    {
        return new RegisterUserDto(
            firstname: $userData['firstname'],
            lastname: $userData['lastname'],
            email: $userData['email'],
            password: $userData['password'],
        );
    }
}
