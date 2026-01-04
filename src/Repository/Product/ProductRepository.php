<?php

namespace App\Repository\Product;

use App\Enum\SortFilterCode;
use Psr\Log\LoggerInterface;
use App\Entity\Product\Product;
use App\Entity\Product\ProductReview;
use App\Dto\Product\RequestFiltersDto;
use App\Dto\Product\ShortProductDto;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    /**
     * Get a paginated list of products based on filters.
     * @param int $page
     * @param int $limit
     * @param RequestFiltersDto $requestFiltersDto
     * @return Paginator
     */
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
            ->getQuery()
            ->getResult();

        $this->logger->debug("ProductRepository::paginateProducts EXIT");

        return new Paginator($query, true);
    }

    /**
     * Get the average ratings for a list of products.
     * @param array<Product> $products
     * @return array<int, float> [productId => avgRating]
     */
    public function getAverageRatingsForProducts(array $products): array
    {
        if (empty($products)) {
            return [];
        }

        $productIds = array_map(fn(ShortProductDto $p) => $p->id, $products);

        $results = $this->createQueryBuilder('p')
            ->select('p.id as productId', 'AVG(pr.rating) as avgRating')
            ->leftJoin('p.productVariants', 'pv')
            ->leftJoin(ProductReview::class, 'pr', 'WITH', 'pr.productVariantId = pv.id')
            ->where('p.id IN (:productIds)')
            ->setParameter('productIds', $productIds)
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();

        $ratings = [];
        foreach ($results as $result) {
            $avgRating = $result['avgRating'] ? round((float) $result['avgRating'], 1) : null;
            $ratings[$result['productId']] = $avgRating;
        }

        return $ratings;
    }
}
