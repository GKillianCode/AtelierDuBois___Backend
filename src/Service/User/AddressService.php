<?php

namespace App\Service\User;

use App\Entity\User\User;
use App\Dto\User\AddressDto;
use App\Entity\User\Address;
use Psr\Log\LoggerInterface;
use App\Mapper\User\AddressMapper;
use App\Manager\User\AddressManager;
use App\Mapper\Request\AddressRequestMapper;
use Symfony\Component\HttpFoundation\Request;

class AddressService
{

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AddressManager $addressManager,
        private readonly AddressMapper $addressMapper,
        private readonly AddressRequestMapper $addressRequestMapper
    ) {}

    /**
     * Add a new address for the user based on the provided AddressDto.
     * @param Request $request
     * @param User $user
     * @return void
     */
    public function addAddress(Request $request, User $user): void
    {
        $this->logger->debug("AddressService::addAddress ENTER");

        $addressDto = $this->addressRequestMapper->mapAddAddressRequest($request);
        $address = $this->addressMapper->toEntityFromDto($addressDto, $user);
        $address = $this->addressManager->setADefaultAddress($address, $user);

        if ($addressDto->isDefault())
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

    public function getAddressInDtoByPublicId(User $user, string $publicId): ?AddressDto
    {
        $this->logger->debug("AddressService::getAddressInDtoByPublicId ENTER");

        $address = $this->addressManager->getAddressByPublicId($user, $publicId);

        if ($address) {
            $addressDto = $this->addressMapper->toDtoFromEntity($address);

            $this->logger->debug("AddressService::getAddressInDtoByPublicId EXIT 1");
            return $addressDto;
        }

        $this->logger->debug("AddressService::getAddressInDtoByPublicId EXIT 2");
        return null;
    }

    public function updateAddress(Request $request, User $user): void
    {
        $this->logger->debug("AddressService::updateAddress ENTER");

        $addressDto = $this->addressRequestMapper->mapUpdateAddressRequest($request);
        $address = $this->addressMapper->toEntityFromDto($addressDto, $user);
        $address = $this->addressManager->setADefaultAddress($address, $user);

        if ($addressDto->isDefault())
            $this->addressManager->unsetAllDefaultAddresses($user);

        $this->addressManager->validateAndSave($address);

        $this->logger->debug("AddressService::updateAddress EXIT");
    }
}
