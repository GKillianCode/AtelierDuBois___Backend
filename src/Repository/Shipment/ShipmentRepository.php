<?php

namespace App\Repository\Shipment;

use App\Entity\Shipment\Shipment;
use App\Entity\User\User;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Shipment>
 */
class ShipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shipment::class);
    }

    /**
     * @return int[]
     */
    public function findDistinctYears(User $user): array
    {
        $rows = $this->createQueryBuilder('s')
            ->select('YEAR(s.createdAt) AS year')
            ->join('s.orderId', 'o')
            ->where('o.userId = :user')
            ->setParameter('user', $user)
            ->distinct()
            ->orderBy('year', 'DESC')
            ->getQuery()
            ->getScalarResult();

        return array_map(static fn(array $row) => (int) $row['year'], $rows);
    }

    public function findByPublicIdForUser(string $publicId, User $user): ?Shipment
    {
        return $this->createQueryBuilder('s')
            ->join('s.orderId', 'o')
            ->where('s.publicId = :publicId')
            ->andWhere('o.userId = :user')
            ->setParameter('publicId', $publicId)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
