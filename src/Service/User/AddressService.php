<?php

namespace App\Service\User;

use App\Entity\User\User;
use App\Dto\User\AddressDto;
use App\Entity\User\Address;
use App\Service\UuidService;
use Psr\Log\LoggerInterface;
use App\Dto\Product\PublicIdDto;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\User\AddressRepository;
use App\Repository\User\AddressTypeRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AddressService
{

    public function __construct(
        public readonly LoggerInterface $logger,
        public readonly EntityManagerInterface $entityManager,
        public readonly UuidService $uuidService,
        public readonly AddressTypeRepository $addressTypeRepository,
        public readonly AddressRepository $addressRepository,
        public readonly ValidatorService $validatorService,
        #[Autowire('%env(int:USER_MAX_ADDRESSES)%')]
        private readonly int $userMaxAddresses = 5
    ) {}

    /**
     * Check if the user can add a new address based on the maximum allowed addresses.
     * @param User $user
     * @return bool
     */
    public function canAddAddress(User $user): bool
    {
        $this->logger->debug("AddressService::canAddAddress ENTER");
        $canAdd = $user->getAddresses()->count() < $this->userMaxAddresses;
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
        $this->validateAddress($address);
        $this->persistAddress($address);

        $this->logger->debug("AddressService::addAddress EXIT");
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

        $newUuidBase62 = $this->uuidService->generateUuid62();

        $address = new Address();
        $address->setUserId($user)
            ->setPublicId($newUuidBase62)
            ->setStreet($addressDto->street)
            ->setZipcode($addressDto->zipcode)
            ->setCity($addressDto->city)
            ->setIsProfessionnal($addressDto->isProfessionnal)
            ->setIsDefault(false)
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

        $violations = $this->validatorService->getViolationsAsArray($address);

        if (!empty($violations)) {
            $this->logger->error("AddressService::validateAddress VALIDATION ERROR");
            throw new \RuntimeException('Validation error while adding address: ' . json_encode($violations));
        }

        $this->logger->debug("AddressService::validateAddress EXIT");
    }

    /**
     * Persist the Address entity to the database.
     * @param Address $address
     * @return void
     */
    private function persistAddress(Address $address): void
    {
        $this->logger->debug("AddressService::persistAddress ENTER");

        $this->entityManager->persist($address);
        $this->entityManager->flush();

        $this->logger->debug("AddressService::persistAddress EXIT");
    }

    public function getAllAddressesInDto(User $user): array
    {
        $this->logger->debug("AddressService::getAllAddresses ENTER");

        $addresses = $user->getAddresses();
        $addressesDto = [];

        foreach ($addresses as $address) {
            $addressDto = $this->createAddressDtoFromAddress($address);
            $addressesDto[] = $addressDto;
        }

        $this->logger->debug("AddressService::getAllAddresses EXIT");
        return $addressesDto;
    }
}
