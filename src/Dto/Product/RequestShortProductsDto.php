<?php

namespace App\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

class RequestShortProductsDto
{
    public function __construct(
        #[Assert\Type(
            type: 'int',
            message: 'La valeur {{ value }} n\'est pas valide pour le numéro de page.'
        )]
        #[Assert\GreaterThan(
            value: 0,
            message: 'Le numéro de page doit être supérieur à {{ value }}.'
        )]
        public readonly int $page,

        #[Assert\Type(
            type: 'int',
            message: 'La valeur {{ value }} n\'est pas valide pour le nombre d\'éléments par page.'
        )]
        #[Assert\GreaterThan(
            value: 10,
            message: 'Le nombre d\'éléments par page doit être supérieur à {{ value }}.'
        )]
        #[Assert\LessThanOrEqual(
            value: 100,
            message: 'Le nombre d\'éléments par page ne doit pas dépasser {{ limit }}.'
        )]
        public readonly int $itemsPerPage,
    ) {}
}
