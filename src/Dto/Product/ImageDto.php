<?php

namespace App\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

class ImageDto
{
    public function __construct(
        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour l\'URL de l\'image.'
        )]
        #[Assert\NotBlank(
            message: 'L\'URL de l\'image ne doit pas être vide.'
        )]
        #[Assert\Url(
            requireTld: true,
            message: 'L\'URL de l\'image n\'est pas une URL valide.'
        )]
        #[Assert\Length(
            max: 255,
            maxMessage: 'L\'URL de l\'image ne doit pas dépasser {{ limit }} caractères.'
        )]
        #[Assert\Regex(
            pattern: '/\.webp$/i',
            message: 'L\'URL de l\'image doit se terminer par .webp'
        )]
        public readonly string $imageUrl,
    ) {}

    public function getPath(): string
    {
        return $this->imageUrl;
    }
}
