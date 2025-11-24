<?php

namespace App\DataFixtures;

use App\Entity\User\AddressType;
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

    public function load(ObjectManager $manager): void {}
}
