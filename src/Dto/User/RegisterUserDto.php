<?php

namespace App\Dto\User;

use App\Validator\Constraints\Password\StrongPassword;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class RegisterUserDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide.', groups: ['registration'])]
        #[Assert\Length(
            min: 2,
            max: 50,
            minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.',
            maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.',
            groups: ['registration']
        )]
        #[Assert\Regex(
            pattern: '/^[a-zA-ZÀ-ÿ\-\s]+$/',
            message: 'Le prénom ne peut contenir que des lettres, espaces et tirets.',
            groups: ['registration']
        )]
        public readonly string $firstname,

        #[Assert\NotBlank(message: 'Le nom de famille ne peut pas être vide.', groups: ['registration'])]
        #[Assert\Length(
            min: 2,
            max: 50,
            minMessage: 'Le nom de famille doit contenir au moins {{ limit }} caractères.',
            maxMessage: 'Le nom de famille ne peut pas dépasser {{ limit }} caractères.',
            groups: ['registration']
        )]
        #[Assert\Regex(
            pattern: '/^[a-zA-ZÀ-ÿ\-\s]+$/',
            message: 'Le nom de famille ne peut contenir que des lettres, espaces et tirets.',
            groups: ['registration']
        )]
        public readonly string $lastname,

        #[Assert\NotNull(message: 'La date de naissance ne peut pas être vide.', groups: ['registration'])]
        #[Assert\Type(type: '\DateTimeImmutable', message: 'La date de naissance doit être une date valide.', groups: ['registration'])]
        #[Assert\LessThan(
            value: '-18 years',
            message: 'Vous devez être majeur pour vous inscrire.',
            groups: ['registration']
        )]
        #[Context([DateTimeNormalizer::FORMAT_KEY => 'YYYY-MM-DD'])]
        public readonly ?\DateTimeImmutable $birthDate = null,

        #[Assert\NotBlank(message: 'L\'email ne peut pas être vide.', groups: ['registration'])]
        #[Assert\Email(message: 'L\'adresse email {{ value }} n\'est pas valide.', groups: ['registration'])]
        #[Assert\Length(
            max: 150,
            maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères.',
            groups: ['registration']
        )]
        public readonly string $email,

        #[StrongPassword(groups: ['registration'])]
        public readonly string $password,
    ) {}
}
