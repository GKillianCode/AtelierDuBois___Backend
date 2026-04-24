<?php

namespace App\Dto\User;

use App\Dto\Types\PublicIdDto;

class AddressDto
{
    public function __construct(
        private ?PublicIdDto $publicId,
        private readonly string $street,
        private readonly string $city,
        private readonly string $zipcode,
        private readonly bool $isProfessional,
        private readonly bool $isDefault,
        private readonly ?string $companyName
    ) {}

    public function getPublicId(): ?PublicIdDto
    {
        return $this->publicId;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    public function isProfessional(): bool
    {
        return $this->isProfessional;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }
}
