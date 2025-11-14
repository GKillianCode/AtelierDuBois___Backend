<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class AddressDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le nom de la rue ne doit pas être vide.')]
        #[Assert\Length(
            max: 255,
            maxMessage: 'Le nom de la rue ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $street,

        #[Assert\NotBlank(message: 'Le nom de la ville ne doit pas être vide.')]
        #[Assert\Length(
            max: 20,
            maxMessage: 'Le nom de la ville ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $city,

        #[Assert\NotBlank(message: 'Le code postal ne doit pas être vide.')]
        #[Assert\NotBlank(message: 'Le code postal ne doit pas être vide.')]
        #[Assert\Length(
            max: 100,
            maxMessage: 'Le code postal ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $zipcode,

        #[Assert\NotNull(message: 'Le type d\'adresse doit être spécifié.')]
        #[Assert\Type(
            type: 'bool',
            message: 'La valeur {{ value }} n\'est pas valide pour le type d\'adresse.'
        )]
        public readonly bool $isProfessionnalAddress
    ) {}
}
