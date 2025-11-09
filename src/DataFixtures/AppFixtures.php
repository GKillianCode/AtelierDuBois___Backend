<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $encoder;

    public function __construct(ValidatorInterface $validator, UserPasswordHasherInterface $encoder)
    {
        $this->validator = $validator;
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {

        $user = new User();
        $user->setUuid('123e4567-e89b-12d3-a456-426614174000');
        $user->setEmail('john.doe@example.com');
        $user->setPlainPassword('Abricot2024!');
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setBirthDate(new \DateTimeImmutable('1990-01-01'));
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($user, null, ['registration']);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                echo $error->getMessage() . "\n";
            }
            return;
        }

        $manager->persist($user);
        $manager->flush();
    }
}
