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
        try {
            $user = new User();
            $user->setUuid($this->uuidUtil->generateUuid())
                ->setUserType(UserType::CUSTOMER)
                ->setFirstname($registerUserDto->getFirstname())
                ->setLastname($registerUserDto->getLastname())
                ->setEmail($registerUserDto->getEmail())
                ->setPlainPassword($registerUserDto->getPassword());

            $this->validateAndSave($user);
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error creating user',
                [
                    'exception' => $e->getMessage(),
                    'email' => $registerUserDto->getEmail()
                ]
            );
        }
    }

    public function update(User $user): void
    {
        try {
            $user->setUpdatedAt(new \DateTimeImmutable());
            $this->validateAndSave($user);
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error updating user',
                [
                    'exception' => $e->getMessage(),
                    'userId' => $user->getId()
                ]
            );
        }
    }

    public function delete(User $user): void
    {
        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error deleting user',
                [
                    'exception' => $e->getMessage(),
                    'userId' => $user->getId()
                ]
            );
        }
    }

    public function emailExists(string $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $userExists = $user === null ? false : true;

        return $userExists;
    }

    use ValidateAndSaveTrait;
}
