<?php

namespace App\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

class PublicIdDto
{
    public function __construct(
        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour le publicId.'
        )]
        #[Assert\NotBlank(
            message: 'Le publicId ne doit pas être vide.'
        )]
        #[Assert\Length(
            min: 22,
            max: 22,
            minMessage: 'Le publicId doit contenir au minimum {{ limit }} caractères.',
            maxMessage: 'Le publicId ne doit pas dépasser {{ limit }} caractères.'
        )]
        #[Assert\Regex(
            pattern: '/^[0-9a-zA-Z_-]{22}$/',
            message: 'Le publicId doit être un UUID valide en Base62.'
        )]
        public readonly string $publicId
    ) {}
}
