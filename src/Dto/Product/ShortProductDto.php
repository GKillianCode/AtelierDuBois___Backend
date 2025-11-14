<?php

namespace App\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

class ShortProductDto
{
    public function __construct(
        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour le titre.'
        )]
        #[Assert\NotBlank(
            message: 'Le titre ne doit pas être vide.'
        )]
        #[Assert\Length(
            min: 2,
            max: 255,
            minMessage: 'Le titre doit contenir au minimum {{ limit }} caractères.',
            maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $title,

        #[Assert\Type(
            type: 'int',
            message: 'La valeur {{ value }} n\'est pas valide pour le prix unitaire.'
        )]
        #[Assert\GreaterThan(
            value: 0,
            message: 'Le prix unitaire doit être supérieur à 0.'
        )]
        public readonly ?int $unitPrice,

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

        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour le publicId.'
        )]
        #[Assert\NotBlank(
            message: 'Le publicId ne doit pas être vide.'
        )]
        #[Assert\Length(
            min: 1,
            max: 100,
            minMessage: 'Le publicId doit contenir au minimum {{ limit }} caractères.',
            maxMessage: 'Le publicId ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $publicId
    ) {}
}
