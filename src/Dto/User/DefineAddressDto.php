<?php

namespace App\Dto\User;

use App\Dto\User\AddressDto;
use Symfony\Component\Validator\Constraints as Assert;

class DefineAddressDto
{
    public function __construct(
        #[Assert\NotNull(
            message: 'L\'adresse de livraison ne doit pas être nulle.'
        )]
        #[Assert\Valid()]
        public readonly AddressDto $deliveryAddress,

        #[Assert\NotNull(
            message: 'L\'adresse de facturation ne doit pas être nulle.'
        )]
        #[Assert\Valid()]
        public readonly ?AddressDto $billingAddress,
    ) {}
}
