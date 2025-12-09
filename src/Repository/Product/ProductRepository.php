<?php

namespace App\Repository\Product;

use App\Enum\SortFilterCode;
use App\Entity\Product\Product;
use App\Dto\Product\RequestFiltersDto;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Product::class);
        $this->logger = $logger;
    }

    public function paginateProducts(int $page, int $limit, RequestFiltersDto $requestFiltersDto): Paginator
    {
        $this->logger->debug("ProductRepository::paginateProducts ENTER with page: $page, limit: $limit, filters: " . json_encode($requestFiltersDto));
        $query = $this->createQueryBuilder('p')
            ->select('p', 'pv', 'i', 'c')
            ->leftJoin('p.productVariants', 'pv', 'WITH', 'pv.isDefault = :isDefault')
            ->leftJoin('pv.images', 'i', 'WITH', 'i.isDefault = :isDefault')
            ->leftJoin('p.categoryId', 'c')
            ->where('LOWER(p.name) LIKE LOWER(:search)');

        if ($requestFiltersDto->categoryPublicId !== null) {
            $query->andWhere('LOWER(c.publicId) = LOWER(:categoryId)')
                ->setParameter('categoryId', $requestFiltersDto->categoryPublicId->publicId);
        }

        $query->setParameter('search', '%' . $requestFiltersDto->search . '%')
            ->setParameter('isDefault', true);

        match ($requestFiltersDto->filter) {
            SortFilterCode::PRICE_ASC => $query->orderBy('pv.price', 'ASC'),
            SortFilterCode::PRICE_DESC => $query->orderBy('pv.price', 'DESC'),
            SortFilterCode::NAME_ASC => $query->orderBy('p.name', 'ASC'),
            SortFilterCode::NAME_DESC => $query->orderBy('p.name', 'DESC'),
            SortFilterCode::CREATED_ASC => $query->orderBy('p.createdAt', 'ASC'),
            SortFilterCode::CREATED_DESC => $query->orderBy('p.createdAt', 'DESC'),
            default => $query->orderBy('p.createdAt', 'ASC')
        };

        match ($requestFiltersDto->productType) {
            SortFilterCode::PRODUCTS_WITH_PRICE => $query->andWhere('pv.price IS NOT NULL'),
            SortFilterCode::PRODUCTS_WITHOUT_PRICE => $query->andWhere('pv.price IS NULL'),
            default => null
        };

        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $this->logger->debug("ProductRepository::paginateProducts EXIT");

        return new Paginator($query, true);
    }
}
