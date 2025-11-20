<?php

namespace App\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDetailDto
{
    public function __construct(
        public readonly ShortProductDto $shortProduct,

        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour la description.'
        )]
        #[Assert\NotBlank(
            message: 'La description ne doit pas être vide.'
        )]
        #[Assert\Length(
            min: 10,
            max: 2000,
            minMessage: 'La description doit contenir au minimum {{ limit }} caractères.',
            maxMessage: 'La description ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $description,

        #[Assert\Type(
            type: 'int',
            message: 'La valeur {{ value }} n\'est pas valide pour le stock.'
        )]
        #[Assert\GreaterThanOrEqual(
            value: 0,
            message: 'Le stock ne doit pas être négatif.'
        )]
        public readonly ?int $stock,

        #[Assert\Type(
            type: 'array',
            message: 'La valeur {{ value }} n\'est pas valide pour les URLs d\'images.'
        )]
        /** @var ImageDto[] */
        public readonly array $imageUrls,
    ) {}
}
