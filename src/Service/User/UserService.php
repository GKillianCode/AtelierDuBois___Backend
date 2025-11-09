<?php

namespace App\Service\User;

use Dom\Entity;
use App\Entity\User;
use App\Dto\User\RegisterUserDto;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{

    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(ValidatorInterface $validator, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function isUserExistsByEmail(string $email): bool
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        return $user === null ? false : true;
    }

    public function registerUser(RegisterUserDto $registerUserDto): void
    {
        $user = new User();
        $user->setUuid(Uuid::v4()->toRfc4122());
        $user->setFirstname($registerUserDto->firstname);
        $user->setLastname($registerUserDto->lastname);
        $user->setBirthDate($registerUserDto->birthDate);
        $user->setEmail($registerUserDto->email);
        $user->setPlainPassword($registerUserDto->password);


        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function checkValidation(RegisterUserDto $registerUserDto): array
    {
        $violations = $this->validator->validate($registerUserDto, null, ['registration']);

        if ($violations->count() === 0) {
            return [];
        }

        $errors = [];
        foreach ($violations as $violation) {
            $property = $violation->getPropertyPath();
            $message = $violation->getMessage();
            $errors[$property][] = $message;
        }

        return $errors;
    }
}
