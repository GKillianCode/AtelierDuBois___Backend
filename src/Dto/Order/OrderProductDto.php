<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

class OrderProductDto
{
    public function __construct(
        #[Assert\Type(
            type: 'int',
            message: 'La valeur {{ value }} n\'est pas valide pour le prix.'
        )]
        #[Assert\GreaterThan(
            value: 0,
            message: 'Le prix unitaire doit être supérieur à 0.'
        )]
        public readonly int $unitPrice,

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
            max: 150,
            maxMessage: 'Le nom du produit ne doit pas dépasser {{ limit }} caractères.'
        )]
        #[Assert\NotBlank(
            message: 'Le nom du produit ne doit pas être vide.'
        )]
        public readonly string $productName,

        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour l\'URL publique.'
        )]
        #[Assert\Length(
            max: 100,
            maxMessage: 'L\'URL publique ne doit pas dépasser {{ limit }} caractères.'
        )]
        #[Assert\NotBlank(
            message: 'L\'URL publique ne doit pas être vide.'
        )]
        public readonly string $publicUrl,

        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour le type de bois.'
        )]
        #[Assert\Length(
            max: 100,
            maxMessage: 'Le nom du bois ne doit pas dépasser {{ limit }} caractères.'
        )]
        #[Assert\NotBlank(
            message: 'Le type de bois ne doit pas être vide.'
        )]
        public readonly string $woodtype
    ) {}
}
