<?php

namespace App\Dto\Product;

use Symfony\Component\Validator\Constraints as Assert;

class PriceDto
{
    public function __construct(
        #[Assert\Type(
            type: 'int',
            message: 'La valeur {{ value }} n\'est pas valide pour le prix.'
        )]
        #[Assert\GreaterThan(
            value: 0,
            message: 'Le prix doit être supérieur à {{ compared_value }}.'
        )]
        #[Assert\LessThan(
            value: 1000000000,
            message: 'Le prix doit être inférieur à {{ compared_value }}.'
        )]
        public readonly int $amount,
    ) {}
}
