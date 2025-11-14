<?php

namespace App\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDetailDto
{
    public function __construct(
        #[Assert\Type(
            type: 'int',
            message: 'La valeur {{ value }} n\'est pas valide pour le nombre d\'éléments par page.'
        )]
        #[Assert\GreaterThan(
            value: 0,
            message: 'Le nombre d\'éléments par page doit être supérieur à 0.'
        )]
        #[Assert\LessThanOrEqual(
            value: 100,
            message: 'Le nombre d\'éléments par page ne doit pas dépasser {{ limit }}.'
        )]
        public readonly string $title,

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
            message: 'La valeur {{ value }} n\'est pas valide pour le prix unitaire.'
        )]
        #[Assert\GreaterThan(
            value: 0,
            message: 'Le prix unitaire doit être supérieur à 0.'
        )]
        public readonly ?int $unitPrice,

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
        public readonly string $publicId,

        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour l\'URL de l\'image principale.'
        )]
        #[Assert\NotBlank(
            message: 'L\'URL de l\'image principale ne doit pas être vide.'
        )]
        #[Assert\Url(
            message: 'L\'URL de l\'image principale n\'est pas une URL valide.'
        )]
        #[Assert\Length(
            max: 255,
            maxMessage: 'L\'URL de l\'image principale ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $mainImageUrl,

        #[Assert\Type(
            type: 'array',
            message: 'La valeur {{ value }} n\'est pas valide pour les URLs d\'images.'
        )]
        #[Assert\All(
            new Assert\Type(type: 'string'),
            new Assert\Url(message: 'L\'URL d\'image n\'est pas valide : {{ value }}.'),
            new Assert\Length(max: 255, maxMessage: 'L\'URL d\'image ne doit pas dépasser {{ limit }} caractères.')
        )]
        /** @var string[] */
        public readonly array $imageUrls,
    ) {}
}
