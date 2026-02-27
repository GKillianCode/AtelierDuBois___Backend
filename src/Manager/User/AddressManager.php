<?php

namespace App\Manager\User;

use App\Dto\User\AddressDto;
use App\Entity\User\User;
use App\Trait\ValidateAndSaveTrait;
use App\Util\ValidatorUtil;
use App\Entity\User\Address;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\User\AddressRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AddressManager
{
    use ValidateAndSaveTrait;
    public function __construct(
        private readonly AddressRepository $addressRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly ValidatorUtil $validatorUtil,
        #[Autowire('%env(int:USER_MAX_ADDRESSES)%')]
        private readonly int $userMaxAddresses = 5
    ) {}

    public function create(Address $address): void
    {
        try {
            $this->validateAndSave($address);
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error creating address',
                [
                    'exception' => $e->getMessage(),
                    'userId' => $address->getUserId()->getId()
                ]
            );
        }
    }

    public function update(Address $address): void
    {
        try {
            $address->setUpdatedAt(new \DateTimeImmutable());
            $this->validateAndSave($address);
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error updating address',
                [
                    'exception' => $e->getMessage(),
                    'userId' => $address->getUserId()->getId()
                ]
            );
        }
    }

    public function delete(Address $address): void
    {
        try {
            $this->entityManager->remove($address);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error deleting address',
                [
                    'exception' => $e->getMessage(),
                    'userId' => $address->getUserId()->getId()
                ]
            );
        }
    }

    /**
     * Check if the user can add a new address based on the maximum allowed addresses.
     * @param User $user
     * @return bool
     */
    public function canUserAddAddress(User $user): bool
    {
        $canAdd = $this->countTheNumberOfAddressesForAUser($user) < $this->userMaxAddresses;
        return $canAdd;
    }

    public function countTheNumberOfAddressesForAUser(User $user): int
    {
        $count = $this->addressRepository->count(['userId' => $user]);
        return $count;
    }

    public function setADefaultAddress(Address $address, User $user): Address
    {
        $countExistingAddresses = $this->countTheNumberOfAddressesForAUser($user);
        if ($countExistingAddresses === 0 || $address->isDefault()) {
            $address->setIsDefault(true);
        }

        return $address;
    }

    public function unsetAllDefaultAddresses(User $user): void
    {
        $this->addressRepository->unsetAllDefaultAddresses($user);
    }

    public function getAddressByPublicId(User $user, string $publicId): ?Address
    {
        $address = $this->addressRepository->findOneBy([
            'userId' => $user,
            'publicId' => $publicId
        ]);

        return $address;
    }

    public function getDefaultAddressForUser(User $user): ?Address
    {
        $address = $this->addressRepository->findOneBy([
            'userId' => $user,
            'isDefault' => true
        ]);

        return $address;
    }

    public function getAllAddresses(User $user): array
    {
        $addresses = $user->getAddresses()->toArray();

        return $addresses;
    }

    public function checkIfAddressExistsForUser(User $user, AddressDto $addressDto): bool
    {
        $address = $this->addressRepository->findOneBy([
            'userId' => $user,
            'street' => $addressDto->getStreet(),
            'zipcode' => $addressDto->getZipcode(),
            'city' => $addressDto->getCity(),
            'isProfessional' => $addressDto->isProfessional(),
        ]);

        return $address !== null;
    }
}
