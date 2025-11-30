<?php

namespace App\Repository\Product;

use App\Entity\Product\ProductVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductVariant>
 */
class ProductVariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVariant::class);
    }

    public function getProductVariantByPublicId(string $publicId): ?ProductVariant
    {
        $productVariant = $this->createQueryBuilder('pv')
            ->select('pv', 'p', 'i', 'w')
            ->leftJoin('pv.productId', 'p')
            ->leftJoin('pv.images', 'i')
            ->leftJoin('pv.woodId', 'w')
            ->where('pv.publicId = :publicId')
            ->setParameter('publicId', $publicId)
            ->getQuery()
            ->getOneOrNullResult();

        return $productVariant;
    }

    public function getAllMinimalProductVariant(string $productId): ?array
    {
        $productVariants = $this->createQueryBuilder('pv')
            ->select('pv', 'p', 'i', 'w')
            ->leftJoin('pv.productId', 'p')
            ->leftJoin('pv.images', 'i')
            ->leftJoin('pv.woodId', 'w')
            ->where('pv.productId = :productId')
            ->andWhere('i.isDefault = true')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getResult();

        return $productVariants;
    }
}
