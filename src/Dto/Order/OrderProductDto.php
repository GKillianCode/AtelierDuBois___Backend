<?php

namespace App\Dto\Order;

use App\Dto\Product\PriceDto;
use App\Dto\Product\PublicIdDto;
use Symfony\Component\Validator\Constraints as Assert;

class OrderProductDto
{
    public function __construct(
        public readonly ?PriceDto $unitPrice,

        #[Assert\Type(
            type: 'integer',
            message: 'La valeur {{ value }} n\'est pas valide pour la quantité.'
        )]
        #[Assert\GreaterThan(
            value: 0,
            message: 'La quantité doit être supérieur à 0.'
        )]
        public readonly int $quantity,

        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour le nom du produit'
        )]
        #[Assert\Length(
            min: 3,
            minMessage: 'Le nom du produit doit contenir au moins {{ limit }} caractères.',
            max: 150,
            maxMessage: 'Le nom du produit ne doit pas dépasser {{ limit }} caractères.'
        )]
        #[Assert\NotBlank(
            message: 'Le nom du produit ne doit pas être vide.'
        )]
        public readonly string $productName,

        public readonly PublicIdDto $publicId,

        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour le type de bois.'
        )]
        #[Assert\Length(
            min: 2,
            minMessage: 'Le nom du bois doit contenir au moins {{ limit }} caractères.',
            max: 100,
            maxMessage: 'Le nom du bois ne doit pas dépasser {{ limit }} caractères.'
        )]
        #[Assert\NotBlank(
            message: 'Le type de bois ne doit pas être vide.'
        )]
        public readonly string $woodtype
    ) {}
}
