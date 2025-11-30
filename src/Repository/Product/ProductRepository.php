<?php

namespace App\Repository\Product;

use App\Enum\SortFilterCode;
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

    public function paginateProducts(int $page, int $limit, SortFilterCode $filter, SortFilterCode $productType): Paginator
    {


        $query = $this->createQueryBuilder('p')
            ->select('p', 'pv', 'i')
            ->leftJoin('p.productVariants', 'pv', 'WITH', 'pv.isDefault = :isDefault')
            ->leftJoin('pv.images', 'i', 'WITH', 'i.isDefault = :isDefault')
            ->setParameter('isDefault', true);

        match ($filter) {
            SortFilterCode::PRICE_ASC => $query->orderBy('pv.price', 'ASC'),
            SortFilterCode::PRICE_DESC => $query->orderBy('pv.price', 'DESC'),
            SortFilterCode::NAME_ASC => $query->orderBy('p.name', 'ASC'),
            SortFilterCode::NAME_DESC => $query->orderBy('p.name', 'DESC'),
            SortFilterCode::CREATED_ASC => $query->orderBy('p.createdAt', 'ASC'),
            SortFilterCode::CREATED_DESC => $query->orderBy('p.createdAt', 'DESC'),
            default => $query->orderBy('p.createdAt', 'ASC')
        };

        match ($productType) {
            SortFilterCode::PRODUCTS_WITH_PRICE => $query->andWhere('pv.price IS NOT NULL'),
            SortFilterCode::PRODUCTS_WITHOUT_PRICE => $query->andWhere('pv.price IS NULL'),
            default => null
        };

        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query, true);
    }
}
