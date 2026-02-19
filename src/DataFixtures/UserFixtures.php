<?php

namespace App\DataFixtures;

use App\Enum\UserType;
use App\Entity\User\User;
use App\Entity\User\Address;
use App\Util\UuidUtil;
use Symfony\Component\Uid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class UserFixtures extends Fixture
{
    public const USER_REFERENCE = 'user';
    public const ADDRESS_REFERENCE = 'address';

    public function __construct(
        private readonly UuidUtil $uuidUtil
    ) {}

    /**
     * Creates sample users and their addresses
     */
    public function load(ObjectManager $manager): void
    {
        $this->createUser($manager);
        $manager->flush();

        $this->createAddresses($manager);
        $manager->flush();
    }

    /**
     * Creates sample users with different roles
     */
    private function createUser(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setUuid(Uuid::v4()->toRfc4122())
                ->setUserType(UserType::CUSTOMER)
                ->setFirstname('prenom' . $i)
                ->setLastname('nom' . $i)
                ->setEmail('example' . $i . '@email.com')
                ->setPlainPassword('Abricot2024!');
            $manager->persist($user);
        }

        $userCustomer = new User();
        $userCustomer->setUuid(Uuid::v4()->toRfc4122())
            ->setUserType(UserType::CUSTOMER)
            ->setFirstname('prenom' . $i)
            ->setLastname('nom' . $i)
            ->setEmail('customer@email.com')
            ->setPlainPassword('Abricot2024!');
        $manager->persist($userCustomer);

        $userInternal = new User();
        $userInternal->setUuid(Uuid::v4()->toRfc4122())
            ->setUserType(UserType::INTERNAL)
            ->setFirstname('prenom' . $i)
            ->setLastname('nom' . $i)
            ->setEmail('internal@email.com')
            ->setPlainPassword('Abricot2024!');
        $manager->persist($userInternal);

        $userAdmin = new User();
        $userAdmin->setUuid(Uuid::v4()->toRfc4122())
            ->setUserType(UserType::ADMIN)
            ->setFirstname('prenom' . $i)
            ->setLastname('nom' . $i)
            ->setEmail('admin@email.com')
            ->setPlainPassword('Abricot2024!');
        $manager->persist($userAdmin);
    }

    /**
     * Creates sample addresses for users
     */
    private function createAddresses(ObjectManager $manager): void
    {
        $users = $this->getUsersFromDatabase($manager);

        $addresses = [
            [
                'street' => '123 Rue de la République',
                'zipcode' => '75001',
                'city' => 'Paris',
                'isDefault' => true,
                'isProfessional' => false,
            ],
            [
                'street' => '45 Avenue des Champs-Élysées',
                'zipcode' => '75008',
                'city' => 'Paris',
                'isDefault' => false,
                'isProfessional' => true,
            ],
            [
                'street' => '78 Boulevard Saint-Michel',
                'zipcode' => '69001',
                'city' => 'Lyon',
                'isDefault' => true,
                'isProfessional' => false,
            ],
            [
                'street' => '12 Place Bellecour',
                'zipcode' => '69002',
                'city' => 'Lyon',
                'isDefault' => false,
                'isProfessional' => false,
            ],
            [
                'street' => '67 Rue de la Canebière',
                'zipcode' => '13001',
                'city' => 'Marseille',
                'isDefault' => true,
                'isProfessional' => false,
            ],
            [
                'street' => '34 Avenue Jean Médecin',
                'zipcode' => '06000',
                'city' => 'Nice',
                'isDefault' => true,
                'isProfessional' => false,
            ],
        ];

        $addressIndex = 0;
        foreach ($users as $index => $user) {
            $numAddresses = mt_rand(1, 3);
            for ($i = 0; $i < $numAddresses; $i++) {
                $addressData = $addresses[($index * $numAddresses + $i) % count($addresses)];

                $address = new Address();
                $address->setPublicId($this->uuidUtil->generateUuid62())
                    ->setStreet($addressData['street'] . ' - User ' . $user->getId())
                    ->setZipcode($addressData['zipcode'])
                    ->setCity($addressData['city'])
                    ->setIsDefault($i === 0)
                    ->setIsProfessional($addressData['isProfessional'])
                    ->setUserId($user);

                $manager->persist($address);
                $this->addReference(self::ADDRESS_REFERENCE . '_' . $addressIndex, $address);
                $addressIndex++;
            }
        }
    }

    /**
     * Retrieves all users from database
     */
    private function getUsersFromDatabase(ObjectManager $manager): array
    {
        return $manager->getRepository(\App\Entity\User\User::class)->findAll();
    }
}
