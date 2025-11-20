<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

class OrderProductsDto
{
    public function __construct(
        #[Assert\Type(
            type: 'array',
            message: 'La valeur {{ value }} n\'est pas un tableau valide de produits de commande.'
        )]
        /**
         * @var OrderProductDto[]
         */
        public readonly array $orderProducts
    ) {}
}
