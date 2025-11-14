<?php

namespace App\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

class ShortProductsDto
{
    public function __construct(
        #[Assert\Type(
            type: 'int',
            message: 'La valeur {{ value }} n\'est pas valide pour le numéro de page.'
        )]
        #[Assert\GreaterThan(
            value: 0,
            message: 'Le numéro de page doit être supérieur à 0.'
        )]
        public readonly int $page,

        #[Assert\Type(
            type: 'bool',
            message: 'La valeur {{ value }} n\'est pas valide pour isFirstPage.'
        )]
        public readonly bool $isFirstPage,

        #[Assert\Type(
            type: 'bool',
            message: 'La valeur {{ value }} n\'est pas valide pour isLastPage.'
        )]
        public readonly bool $isLastPage,

        #[Assert\Type(
            type: 'int',
            message: 'La valeur {{ value }} n\'est pas valide pour le nombre de pages.'
        )]
        #[Assert\GreaterThan(
            value: 0,
            message: 'Le nombre de pages doit être supérieur à 0.'
        )]
        public readonly int $nbPages,

        #[Assert\Type(
            type: 'array',
            message: 'La valeur {{ value }} n\'est pas valide pour les produits.'
        )]
        #[Assert\All(
            new Assert\Valid()
        )]
        /** @var ShortProductDto[] */
        public readonly array $products
    ) {}
}
