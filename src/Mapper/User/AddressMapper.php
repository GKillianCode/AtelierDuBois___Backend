<?php

namespace App\Mapper\User;

use App\Util\UuidUtil;
use App\Entity\User\User;
use App\Dto\User\AddressDto;
use App\Entity\User\Address;
use Psr\Log\LoggerInterface;
use App\Dto\Types\PublicIdDto;

class AddressMapper
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UuidUtil $uuidUtil,
    ) {}

    public function toEntityFromDto(AddressDto $addressDto, User $user): Address
    {
        $this->logger->debug("AddressMapper::toEntityFromDto ENTER");

        $address = new Address();
        $address->setUserId($user)
            ->setPublicId($addressDto->getPublicId() ? $addressDto->getPublicId()->getPublicId() : $this->uuidUtil->generateUuid62())
            ->setStreet($addressDto->getStreet())
            ->setZipcode($addressDto->getZipcode())
            ->setCity($addressDto->getCity())
            ->setIsProfessionnal($addressDto->isProfessionnal())
            ->setIsDefault($addressDto->isDefault());

        $this->logger->debug("AddressMapper::toEntityFromDto EXIT");
        return $address;
    }

    public function toDtoFromEntity(Address $address): AddressDto
    {
        $this->logger->debug("AddressMapper::toDtoFromEntity ENTER");

        $addressDto = new AddressDto(
            publicId: new PublicIdDto($address->getPublicId()),
            street: $address->getStreet(),
            city: $address->getCity(),
            zipcode: $address->getZipcode(),
            isProfessionnal: $address->isProfessionnal(),
            isDefault: $address->isDefault()
        );

        $this->logger->debug("AddressMapper::toDtoFromEntity EXIT");
        return $addressDto;
    }
}
