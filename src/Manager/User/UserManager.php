<?php

namespace App\Manager\User;

use App\Enum\UserType;
use App\Entity\User\User;
use App\Util\ValidatorUtil;
use Psr\Log\LoggerInterface;
use App\Dto\Register\RegisterUserDto;
use App\Trait\ValidateAndSaveTrait;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly ValidatorUtil $validatorUtil
    ) {}

    public function create(RegisterUserDto $registerUserDto): User
    {
        $this->logger->debug("UserService::registerUser ENTER");
        $user = new User();
        $user->setUserType(UserType::CUSTOMER);
        $user->setFirstname($registerUserDto->firstname);
        $user->setLastname($registerUserDto->lastname);
        $user->setEmail($registerUserDto->email);
        $user->setPlainPassword($registerUserDto->password);

        $this->logger->debug("UserService::registerUser EXIT");

        return $user;
    }

    public function update(User $user): void
    {
        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
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
