<?php

namespace App\Manager\User;

use App\Entity\User\User;
use App\Util\ValidatorUtil;
use App\Entity\User\Address;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\User\AddressRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AddressManager
{
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
        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }

    public function update(Address $address): void
    {
        $address->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function delete(Address $address): void
    {
        $this->entityManager->remove($address);
        $this->entityManager->flush();
    }

    public function validateAndSave(Address $address): void
    {
        $violations = $this->validatorUtil->getViolationsAsArray($address);
        if (!empty($violations)) {
            $this->logger->error("AddressManager::validateAndSave VALIDATION ERROR");
            throw new \RuntimeException('Validation error while adding address: ' . json_encode($violations));
        }
        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }

    /**
     * Check if the user can add a new address based on the maximum allowed addresses.
     * @param User $user
     * @return bool
     */
    public function canUserAddAddress(User $user): bool
    {
        $this->logger->debug("AddressManager::canAddAddress ENTER");
        $canAdd = $this->countTheNumberOfAddressesForAUser($user) < $this->userMaxAddresses;
        $this->logger->debug("AddressManager::canAddAddress EXIT");
        return $canAdd;
    }

    public function countTheNumberOfAddressesForAUser(User $user): int
    {
        $this->logger->debug("AddressManager::countTheNumberOfAddressesForAUser ENTER");
        $count = $this->addressRepository->count(['userId' => $user]);
        $this->logger->debug("AddressManager::countTheNumberOfAddressesForAUser EXIT");
        return $count;
    }

    public function setADefaultAddress(Address $address, User $user): Address
    {
        $this->logger->debug("AddressManager::setADefaultAddress ENTER");

        $countExistingAddresses = $this->countTheNumberOfAddressesForAUser($user);
        if ($countExistingAddresses === 0 || $address->isDefault()) {
            $address->setIsDefault(true);
        }

        $this->logger->debug("AddressManager::setADefaultAddress EXIT");

        return $address;
    }

    public function unsetAllDefaultAddresses(User $user): void
    {
        $this->logger->debug("AddressManager::unsetAllDefaultAddresses ENTER");
        $this->addressRepository->unsetAllDefaultAddresses($user);
        $this->logger->debug("AddressManager::unsetAllDefaultAddresses EXIT");
    }

    public function getAddressByPublicId(User $user, string $publicId): ?Address
    {
        $this->logger->debug("AddressManager::getAddressByPublicId ENTER");

        $address = $this->addressRepository->findOneBy([
            'userId' => $user,
            'publicId' => $publicId
        ]);

        $this->logger->debug("AddressManager::getAddressByPublicId EXIT");
        return $address;
    }

    public function getDefaultAddressForUser(User $user): ?Address
    {
        $this->logger->debug("AddressManager::getDefaultAddressForUser ENTER");

        $address = $this->addressRepository->findOneBy([
            'userId' => $user,
            'isDefault' => true
        ]);

        $this->logger->debug("AddressManager::getDefaultAddressForUser EXIT");
        return $address;
    }

    public function getAllAddresses(User $user): array
    {
        $this->logger->debug("AddressManager::getAllAddresses ENTER");
        $addresses = $user->getAddresses()->toArray();
        $this->logger->debug("AddressManager::getAllAddresses EXIT");
        return $addresses;
    }
}
