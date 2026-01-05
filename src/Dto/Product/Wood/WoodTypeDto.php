<?php

namespace App\Dto\Product\Wood;

use App\Dto\Types\ImageDto;
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
        #[Assert\Regex(
            pattern: '/^[A-Z]+$/',
            message: 'Le nom du bois ne doit contenir que des lettres majuscules'
        )]
        public readonly string $name,

        public readonly ImageDto $mainImageUrl,
    ) {}
}
