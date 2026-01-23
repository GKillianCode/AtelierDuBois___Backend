<?php

namespace App\Service\User;

use App\Entity\User\User;
use App\Entity\User\Address;
use Psr\Log\LoggerInterface;
use App\Mapper\User\AddressMapper;
use App\Manager\User\AddressManager;
use App\Dto\Register\RegisterAddressDto;
use App\Dto\Register\ResponseAddressDto;

class AddressService
{

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AddressManager $addressManager,
        private readonly AddressMapper $addressMapper
    ) {}

    /**
     * Add a new address for the user based on the provided AddressDto.
     * @param RegisterAddressDto $addressDto
     * @param User $user
     * @return void
     */
    public function addAddress(RegisterAddressDto $registerAddressDto, User $user): void
    {
        $this->logger->debug("AddressService::addAddress ENTER");

        $address = $this->addressMapper->toEntityFromDto($registerAddressDto, $user);
        $address = $this->addressManager->setADefaultAddress($address, $user);

        if ($registerAddressDto->isDefault)
            $this->addressManager->unsetAllDefaultAddresses($user);

        $this->addressManager->validateAndSave($address);
        $this->logger->debug("AddressService::addAddress EXIT");
    }

    public function getAllAddressesInDto(User $user): array
    {
        $this->logger->debug("AddressService::getAllAddresses ENTER");

        $addresses = $this->addressManager->getAllAddresses($user);
        $addressesDto = [];

        foreach ($addresses as $address) {
            $addressDto = $this->addressMapper->toDtoFromEntity($address);
            $addressesDto[] = $addressDto;
        }

        $this->logger->debug("AddressService::getAllAddresses EXIT");
        return $addressesDto;
    }

    public function getAddressInDtoByPublicId(User $user, string $publicId): ?ResponseAddressDto
    {
        $this->logger->debug("AddressService::getAddressInDtoByPublicId ENTER");

        $responseAddressDto = $this->addressManager->getAddressByPublicId($user, $publicId);

        if ($responseAddressDto) {
            $addressDto = $this->addressMapper->toDtoFromEntity($responseAddressDto);
            $this->logger->debug("AddressService::getAddressInDtoByPublicId EXIT 1");
            return $addressDto;
        }

        $this->logger->debug("AddressService::getAddressInDtoByPublicId EXIT 2");
        return null;
    }

    public function updateAddress(Address $address, RegisterAddressDto $registerAddressUpdateDto, User $user): void
    {
        $this->logger->debug("AddressService::updateAddress ENTER");

        $address = $this->addressMapper->toEntityFromDto($registerAddressUpdateDto, $user);
        $address = $this->addressManager->setADefaultAddress($address, $user);

        if ($registerAddressUpdateDto->isDefault)
            $this->addressManager->unsetAllDefaultAddresses($user);

        $this->addressManager->validateAndSave($address);

        $this->logger->debug("AddressService::updateAddress EXIT");
    }
}
