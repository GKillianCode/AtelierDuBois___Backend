<?php

namespace App\Repository\User;

use App\Entity\User\User;
use App\Entity\User\Address;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Address>
 */
class AddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    public function unsetAllDefaultAddresses(User $user): void
    {
        $this->createQueryBuilder('a')
            ->update()
            ->set('a.isDefault', ':isDefault')
            ->where('a.userId = :user')
            ->andWhere('a.isDefault = :currentDefault')
            ->setParameter('isDefault', false)
            ->setParameter('currentDefault', true)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
