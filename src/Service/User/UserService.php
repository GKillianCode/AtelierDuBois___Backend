<?php

namespace App\Service\User;

use App\Dto\Request\RegisterUserDto;
use App\Manager\User\UserManager;
use App\Repository\User\UserRepository;
use App\Util\ValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class UserService
{
    public function __construct(
        public readonly LoggerInterface $logger,
        public readonly EntityManagerInterface $entityManager,
        private UserManager $userManager,
        public readonly ValidatorUtil $validatorUtil,
        public readonly UserRepository $userRepository,
    ) {}

    public function registerUser(RegisterUserDto $registerUserDto): void
    {
        $this->logger->debug("UserService::registerUser ENTER");

        if ($this->checkEmailExists($registerUserDto->getEmail())) {
            $this->logger->debug("UserService::registerUser EXIT 1");
            throw new \Exception('User already exists.');
        }

        $this->userManager->create($registerUserDto);
        $this->logger->debug("UserService::registerUser EXIT");
    }

    public function checkEmailExists(string $email): bool
    {
        return $this->userManager->emailExists($email);
    }
}
