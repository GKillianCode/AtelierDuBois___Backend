<?php

namespace App\Dto\User;

use App\Dto\Register\ResponseAddressDto;
use Symfony\Component\Validator\Constraints as Assert;

class DefineAddressDto
{
    public function __construct(
        #[Assert\NotNull(
            message: 'L\'adresse de livraison ne doit pas être nulle.'
        )]
        #[Assert\Valid()]
        public readonly ResponseAddressDto $deliveryAddress,

        #[Assert\NotNull(
            message: 'L\'adresse de facturation ne doit pas être nulle.'
        )]
        #[Assert\Valid()]
        public readonly ?ResponseAddressDto $billingAddress,
    ) {}
}
