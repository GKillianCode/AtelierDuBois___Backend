<?php

namespace App\Manager\User;

use App\Dto\Request\RegisterUserDto;
use App\Entity\User\User;
use App\Enum\UserType;
use App\Repository\User\UserRepository;
use App\Trait\ValidateAndSaveTrait;
use App\Util\UuidUtil;
use App\Util\ValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class UserManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly ValidatorUtil $validatorUtil,
        private readonly UuidUtil $uuidUtil
    ) {}

    public function create(RegisterUserDto $registerUserDto): void
    {
        $this->logger->debug("UserManager::registerUser ENTER");
        $user = new User();
        $user->setUuid($this->uuidUtil->generateUuid())
            ->setUserType(UserType::CUSTOMER)
            ->setFirstname($registerUserDto->getFirstname())
            ->setLastname($registerUserDto->getLastname())
            ->setEmail($registerUserDto->getEmail())
            ->setPlainPassword($registerUserDto->getPassword());

        $this->validateAndSave($user);
        $this->logger->debug("UserManager::registerUser EXIT");
    }

    public function update(User $user): void
    {
        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->validateAndSave($user);
    }

    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function emailExists(string $email): bool
    {
        $this->logger->debug("UserManager::emailExists ENTER");
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $userExists = $user === null ? false : true;
        $this->logger->debug("UserManager::emailExists EXIT");
        return $userExists;
    }

    use ValidateAndSaveTrait;
}
