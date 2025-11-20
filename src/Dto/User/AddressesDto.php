<?php

namespace App\Dto\User;

class AddressesDto
{
    public function __construct(
        /** @var AddressDto[] */
        public readonly array $addresses
    ) {}
}
