<?php

namespace App\Dto\Types;

use Symfony\Component\Validator\Constraints as Assert;

class PriceDto
{
    public function __construct(
        #[Assert\Type(
            type: 'int',
            message: 'price.type'
        )]
        #[Assert\GreaterThan(
            value: 0,
            message: 'price.greater_than'
        )]
        #[Assert\LessThan(
            value: 1000000000,
            message: 'price.less_than'
        )]
        private readonly int $amount,
    ) {}

    public function getAmount(): int
    {
        return $this->amount;
    }
}
