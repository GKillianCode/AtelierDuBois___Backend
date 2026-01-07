<?php

namespace App\Dto\User;

use App\Dto\Types\PublicIdDto;
use Symfony\Component\Validator\Constraints as Assert;

class AddressDto
{
    public function __construct(
        public ?PublicIdDto $publicId = null,

        #[Assert\NotBlank(message: 'Le nom de la rue ne doit pas être vide.')]
        #[Assert\Length(
            min: 2,
            max: 255,
            minMessage: 'Le nom de la rue doit contenir au moins {{ limit }} caractères.',
            maxMessage: 'Le nom de la rue ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $street,

        #[Assert\NotBlank(message: 'Le nom de la ville ne doit pas être vide.')]
        #[Assert\Length(
            min: 2,
            max: 20,
            minMessage: 'Le nom de la ville doit contenir au moins {{ limit }} caractères.',
            maxMessage: 'Le nom de la ville ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $city,

        #[Assert\NotBlank(message: 'Le code postal ne doit pas être vide.')]
        #[Assert\Length(
            max: 100,
            maxMessage: 'Le code postal ne doit pas dépasser {{ limit }} caractères.'
        )]
        #[Assert\Regex(
            pattern: '/^[A-Z0-9\s]+$/',
            message: 'Le code postal ne doit contenir que des lettres majuscules, des chiffres et des espaces.'
        )]
        public readonly string $zipcode,

        #[Assert\NotNull(message: 'Le type d\'adresse doit être spécifié.')]
        #[Assert\Type(
            type: 'bool',
            message: 'La valeur {{ value }} n\'est pas valide pour le type d\'adresse.'
        )]
        public readonly bool $isProfessionnal,

        #[Assert\NotNull(message: 'Le statut par défaut doit être spécifié.')]
        #[Assert\Type(
            type: 'bool',
            message: 'La valeur {{ value }} n\'est pas valide pour le statut par défaut.'
        )]
        public readonly bool $isDefault
    ) {}
}
