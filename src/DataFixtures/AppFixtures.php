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

    public function load(ObjectManager $manager): void
    {

        $addressTypes = ['PERSONNAL', 'PROFESSIONAL'];

        foreach ($addressTypes as $type) {
            $addressType = new AddressType();
            $addressType->setName($type)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable());

            $errors = $this->validator->validate($addressType, null, ['registration']);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    echo $error->getMessage() . "\n";
                }
                return;
            }

            $manager->persist($addressType);
        }

        $manager->flush();
    }
}
