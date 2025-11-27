<?php

namespace App\Repository\Product;

use App\Entity\Product\Product;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function paginateProducts(int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('p')
            ->select('p', 'pv', 'i')
            ->leftJoin('p.productVariants', 'pv', 'WITH', 'pv.isDefault = :isDefault')
            ->leftJoin('pv.images', 'i', 'WITH', 'i.isDefault = :isDefault')
            ->setParameter('isDefault', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query, true);
    }
}
