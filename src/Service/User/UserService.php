<?php

namespace App\Service\User;

use App\Util\ValidatorUtil;
use Psr\Log\LoggerInterface;
use App\Dto\User\RegisterUserDto;
use App\Manager\User\UserManager;
use App\Repository\User\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

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
        $user = $this->userManager->create($registerUserDto);
        $this->userManager->validateAndSave($user);
        $this->logger->debug("UserService::registerUser EXIT");
    }
}
