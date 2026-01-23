<?php

namespace App\Mapper\User;

use App\Util\UuidUtil;
use App\Entity\User\User;
use App\Entity\User\Address;
use Psr\Log\LoggerInterface;
use App\Dto\Types\PublicIdDto;
use App\Dto\Register\RegisterAddressDto;
use App\Dto\Register\ResponseAddressDto;

class AddressMapper
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UuidUtil $uuidUtil,
    ) {}

    public function toEntityFromDto(RegisterAddressDto $registerAddressDto, User $user)
    {
        $this->logger->debug("AddressMapper::toEntityFromDto ENTER");

        $newUuidBase62 = $this->uuidUtil->generateUuid62();

        $address = new Address();
        $address->setUserId($user)
            ->setPublicId($newUuidBase62)
            ->setStreet($registerAddressDto->street)
            ->setZipcode($registerAddressDto->zipcode)
            ->setCity($registerAddressDto->city)
            ->setIsProfessionnal($registerAddressDto->isProfessionnal)
            ->setIsDefault($registerAddressDto->isDefault);

        $this->logger->debug("AddressMapper::toEntityFromDto EXIT");
        return $address;
    }

    public function toDtoFromEntity(Address $address): ResponseAddressDto
    {
        $this->logger->debug("AddressMapper::toDtoFromEntity ENTER");

        $responseAddressDto = new ResponseAddressDto(
            publicId: new PublicIdDto($address->getPublicId()),
            street: $address->getStreet(),
            city: $address->getCity(),
            zipcode: $address->getZipcode(),
            isProfessionnal: $address->isProfessionnal(),
            isDefault: $address->isDefault()
        );

        $this->logger->debug("AddressMapper::toDtoFromEntity EXIT");
        return $responseAddressDto;
    }
}
