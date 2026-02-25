<?php

namespace App\Mapper\Request;

use App\Dto\Request\RegisterUserDto;
use App\Validator\Constraints\Password\StrongPassword;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterUserRequestMapper
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    public function registerUserRequest(Request $request): RegisterUserDto
    {
        $userData = $this->extractJsonData($request);
        $this->validateParameters($userData);

        return $this->createUserDto($userData);
    }

    private function extractJsonData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException('Invalid JSON data: ' . json_last_error_msg());
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

    private function validateParameters(array $data): void
    {
        $constraints = new Assert\Collection([
            'firstname' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length([
                    'min' => 2,
                    'max' => 50,
                    'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                    'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères.',
                ]),
                new Assert\Regex([
                    'pattern' => '/^[a-zA-ZÀ-ÿ\-\s]+$/',
                    'message' => 'Le prénom ne peut contenir que des lettres, espaces et tirets.',
                ]),
            ],
            'lastname' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length([
                    'min' => 2,
                    'max' => 50,
                    'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                    'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                ]),
                new Assert\Regex([
                    'pattern' => '/^[a-zA-ZÀ-ÿ\-\s]+$/',
                    'message' => 'Le nom ne peut contenir que des lettres, espaces et tirets.',
                ]),
            ],
            'email' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Email([
                    'message' => 'L\'adresse email {{ value }} n\'est pas valide.',
                ]),
                new Assert\Length([
                    'min' => 8,
                    'max' => 150,
                    'minMessage' => 'L\'email doit contenir au moins {{ limit }} caractères.',
                    'maxMessage' => 'L\'email ne peut pas dépasser {{ limit }} caractères.',
                ]),
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new StrongPassword(),
            ],
            'confirmPassword' => [
                new Assert\NotBlank(message: 'La confirmation du mot de passe ne peut pas être vide.'),
                new Assert\Type('string'),
                new StrongPassword(),
                new Assert\EqualTo([
                    'value' => $data['password'] ?? null,
                    'message' => 'Les mots de passe ne correspondent pas.',
                ]),
            ],
        ]);

        $violations = $this->validator->validate($data, $constraints);

        if (\count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new BadRequestException('Validation failed: ' . implode(', ', $errors));
        }
    }

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
