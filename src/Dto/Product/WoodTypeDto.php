<?php

namespace App\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

class WoodTypeDto
{
    public function __construct(
        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour le nom du bois.'
        )]
        #[Assert\NotBlank(
            message: 'Le nom du bois ne doit pas être vide.'
        )]
        #[Assert\Length(
            min: 2,
            max: 100,
            minMessage: 'Le nom du bois doit contenir au minimum {{ limit }} caractères.',
            maxMessage: 'Le nom du bois ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $name,

        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour l\'URL de l\'image.'
        )]
        #[Assert\NotBlank(
            message: 'L\'URL de l\'image ne doit pas être vide.'
        )]
        #[Assert\Url(
            message: 'L\'URL de l\'image n\'est pas une URL valide.'
        )]
        #[Assert\Length(
            max: 255,
            maxMessage: 'L\'URL de l\'image ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $mainImageUrl,
    ) {}
}
