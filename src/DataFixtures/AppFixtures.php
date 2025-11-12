<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AppFixtures extends Fixture
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function load(ObjectManager $manager): void
    {

        $user = new User();
        $user->setUuid('123e4567-e89b-12d3-a456-426614174000');
        $user->setEmail('john.doe@example.com');
        $user->setPlainPassword('Abricot2024!');
        $user->setFirstname('John');
        $user->setLastname('Doe');
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
