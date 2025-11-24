<?php

namespace App\Service\User;

use App\Entity\User\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use App\Dto\User\RegisterUserDto;
use App\Service\ValidatorService;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        public readonly LoggerInterface $logger,
        public readonly EntityManagerInterface $entityManager,
        public readonly ValidatorService $validatorService,
        public readonly UserRepository $userRepository,
    ) {}

    public function isUserExistsByEmail(string $email): bool
    {
        $this->logger->debug("UserService::isUserExistsByEmail ENTER");
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $userExists = $user === null ? false : true;
        $this->logger->debug("UserService::isUserExistsByEmail EXIT");
        return $userExists;
    }

    public function registerUser(RegisterUserDto $registerUserDto): void
    {
        $this->logger->debug("UserService::registerUser ENTER");
        $user = new User();
        $user->setUuid(Uuid::v4()->toRfc4122());
        $user->setFirstname($registerUserDto->firstname);
        $user->setLastname($registerUserDto->lastname);
        $user->setEmail($registerUserDto->email);
        $user->setPlainPassword($registerUserDto->password);

        $this->validateUser($user);
        $this->persistUser($user);
        $this->logger->debug("UserService::registerUser EXIT");
    }

    private function validateUser(User $user): void
    {
        $this->logger->debug("UserService::validateUser ENTER");

        $violations = $this->validatorService->getViolationsAsArray($user);
        if (!empty($violations)) {
            $this->logger->error("UserService::validateUser VALIDATION ERROR");
            throw new \RuntimeException('Validation error while adding user: ' . json_encode($violations));
        }

        $this->logger->debug("UserService::validateUser EXIT");
    }

    /**
     * Persist the User entity to the database.
     * @param User $user
     * @return void
     */
    private function persistUser(User $user): void
    {
        $this->logger->debug("UserService::persistUser ENTER");

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->logger->debug("UserService::persistUser EXIT");
    }
}
