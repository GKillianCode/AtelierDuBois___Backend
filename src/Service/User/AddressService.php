<?php

namespace App\Service\User;

use App\Util\UuidUtil;
use App\Entity\User\User;
use App\Dto\User\AddressDto;
use App\Entity\User\Address;
use App\Service\UuidService;
use Psr\Log\LoggerInterface;
use App\Dto\Types\PublicIdDto;
use App\Manager\User\AddressManager;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\User\AddressRepository;
use App\Util\ValidatorUtil;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AddressService
{

    public function __construct(
        public readonly LoggerInterface $logger,
        public readonly EntityManagerInterface $entityManager,
        public readonly AddressRepository $addressRepository,
        private readonly AddressManager $addressManager,
        public readonly UuidUtil $uuidUtil,
        public readonly ValidatorUtil $validatorUtil,
        #[Autowire('%env(int:USER_MAX_ADDRESSES)%')]
        private readonly int $userMaxAddresses = 5
    ) {}

    public function countUserAddresses(User $user): int
    {
        $this->logger->debug("AddressService::countUserAddresses ENTER");
        $count = $this->addressRepository->count(['userId' => $user]);
        $this->logger->debug("AddressService::countUserAddresses EXIT");
        return $count;
    }

    /**
     * Check if the user can add a new address based on the maximum allowed addresses.
     * @param User $user
     * @return bool
     */
    public function canAddAddress(User $user): bool
    {
        $this->logger->debug("AddressService::canAddAddress ENTER");
        $canAdd = $this->countUserAddresses($user) < $this->userMaxAddresses;
        $this->logger->debug("AddressService::canAddAddress EXIT");
        return $canAdd;
    }

    /**
     * Add a new address for the user based on the provided AddressDto.
     * @param AddressDto $addressDto
     * @param User $user
     * @return void
     */
    public function addAddress(AddressDto $addressDto, User $user): void
    {
        $this->logger->debug("AddressService::addAddress ENTER");

        $address = $this->createAddressFromDto($addressDto, $user);
        $address = $this->setAddressAsDefault($address, $user);

        if ($addressDto->isDefault)
            $this->setAddressesAsNonDefault($user);

        $this->addressManager->create($address);

        $this->logger->debug("AddressService::addAddress EXIT");
    }

    private function setAddressAsDefault(Address $address, User $user): Address
    {
        $this->logger->debug("AddressService::setAddressAsDefault ENTER");

        $countExistingAddresses = $this->countUserAddresses($user);
        if ($countExistingAddresses === 0 || $address->isDefault()) {
            $address->setIsDefault(true);
        }

        $this->logger->debug("AddressService::setAddressAsDefault EXIT");

        return $address;
    }

    private function setAddressesAsNonDefault(User $user): void
    {
        $this->logger->debug("AddressService::setAddressesAsNonDefault ENTER");
        $this->addressRepository->unsetAllDefaultAddresses($user);
        $this->logger->debug("AddressService::setAddressAsNonDefault EXIT");
    }

    /**
     * Create an Address entity from the provided AddressDto and User.
     * @param AddressDto $addressDto
     * @param User $user
     * @return Address
     */
    private function createAddressFromDto(AddressDto $addressDto, User $user): Address
    {
        $this->logger->debug("AddressService::createAddressFromDto ENTER");

        $newUuidBase62 = $this->uuidUtil->generateUuid62();

        $address = new Address();
        $address->setUserId($user)
            ->setPublicId($newUuidBase62)
            ->setStreet($addressDto->street)
            ->setZipcode($addressDto->zipcode)
            ->setCity($addressDto->city)
            ->setIsProfessionnal($addressDto->isProfessionnal)
            ->setIsDefault($addressDto->isDefault)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->logger->debug("AddressService::createAddressFromDto EXIT");
        return $address;
    }

    public function createAddressDtoFromAddress(Address $address): AddressDto
    {
        $this->logger->debug("AddressService::createAddressDtoFromAddress ENTER");

        $addressDto = new AddressDto(
            publicId: new PublicIdDto($address->getPublicId()),
            street: $address->getStreet(),
            city: $address->getCity(),
            zipcode: $address->getZipcode(),
            isProfessionnal: $address->isProfessionnal(),
            isDefault: $address->isDefault()
        );

        $this->logger->debug("AddressService::createAddressDtoFromAddress EXIT");
        return $addressDto;
    }

    /**
     * Validate the Address entity.
     * @param Address $address
     * @return void
     */
    private function validateAddress(Address $address): void
    {
        $this->logger->debug("AddressService::validateAddress ENTER");

        $violations = $this->validatorUtil->getViolationsAsArray($address);

        if (!empty($violations)) {
            $this->logger->error("AddressService::validateAddress VALIDATION ERROR");
            throw new \RuntimeException('Validation error while adding address: ' . json_encode($violations));
        }

        $this->logger->debug("AddressService::validateAddress EXIT");
    }

    public function getAddressByPublicId(User $user, string $publicId): ?Address
    {
        $this->logger->debug("AddressService::getAddressByPublicId ENTER");

        $address = $this->addressRepository->findOneBy([
            'userId' => $user,
            'publicId' => $publicId
        ]);

        $this->logger->debug("AddressService::getAddressByPublicId EXIT");
        return $address;
    }

    public function getDefaultAddressForUser(User $user): ?Address
    {
        $this->logger->debug("AddressService::getDefaultAddress ENTER");

        $address = $this->addressRepository->findOneBy([
            'userId' => $user,
            'isDefault' => true
        ]);

        $this->logger->debug("AddressService::getDefaultAddress EXIT");
        return $address;
    }

    public function getAllAddresses(User $user): array
    {
        $this->logger->debug("AddressService::getAllAddresses ENTER");
        $addresses = $user->getAddresses()->toArray();
        $this->logger->debug("AddressService::getAllAddresses EXIT");
        return $addresses;
    }

    public function getAllAddressesInDto(User $user): array
    {
        $this->logger->debug("AddressService::getAllAddresses ENTER");

        $addresses = $this->getAllAddresses($user);
        $addressesDto = [];

        foreach ($addresses as $address) {
            $addressDto = $this->createAddressDtoFromAddress($address);
            $addressesDto[] = $addressDto;
        }

        $this->logger->debug("AddressService::getAllAddresses EXIT");
        return $addressesDto;
    }

    public function getAddressInDtoByPublicId(User $user, string $publicId): ?AddressDto
    {
        $this->logger->debug("AddressService::getAddressInDtoByPublicId ENTER");

        $address = $this->getAddressByPublicId($user, $publicId);

        if ($address) {
            $addressDto = $this->createAddressDtoFromAddress($address);
            $this->logger->debug("AddressService::getAddressInDtoByPublicId EXIT 1");
            return $addressDto;
        }

        $this->logger->debug("AddressService::getAddressInDtoByPublicId EXIT 2");
        return null;
    }

    public function updateAddress(Address $address, AddressDto $addressUpdateDto, User $user): void
    {
        $this->logger->debug("AddressService::updateAddress ENTER");

        $address->setStreet($addressUpdateDto->street)
            ->setZipcode($addressUpdateDto->zipcode)
            ->setCity($addressUpdateDto->city)
            ->setIsProfessionnal($addressUpdateDto->isProfessionnal)
            ->setUpdatedAt(new \DateTimeImmutable());

        $address = $this->setAddressAsDefault($address, $user);

        if ($addressUpdateDto->isDefault)
            $this->setAddressesAsNonDefault($user);

        $this->validateAddress($address);

        $this->addressManager->update($address);

        $this->logger->debug("AddressService::updateAddress EXIT");
    }

    public function removeAddressByPublicId(Address $address): void
    {
        $this->logger->debug("AddressService::removeAddressByPublicId ENTER");
        $this->addressManager->delete($address);
        $this->logger->debug("AddressService::removeAddressByPublicId EXIT");
    }
}
