<?php

namespace App\Repository\Product;

use Psr\Log\LoggerInterface;
use App\Entity\Product\Product;
use App\Entity\Product\ProductReview;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Dto\Response\ResponseResumeProductDto;
use App\Enum\SortFilter\ProductSortFilterCode;
use App\Dto\Request\Filter\GetAllProductsRequestDto;
use App\Dto\Product\RequestFilter\RequestProductFiltersDto;
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
     * @param GetAllProductsRequestDto $getAllProductsRequestDto
     * @return Paginator
     */
    public function paginateProducts(GetAllProductsRequestDto $getAllProductsRequestDto): Paginator
    {
        $this->logger->debug("ProductRepository::paginateProducts ENTER");
        $query = $this->createQueryBuilder('p')
            ->select('p', 'pv', 'i', 'c')
            ->leftJoin('p.productVariants', 'pv', 'WITH', 'pv.isDefault = :isDefault')
            ->leftJoin('pv.images', 'i', 'WITH', 'i.isDefault = :isDefault')
            ->leftJoin('p.categoryId', 'c')
            ->where('LOWER(p.name) LIKE LOWER(:search)');

        if ($getAllProductsRequestDto->getCategoryPublicId() !== null) {
            $query->andWhere('LOWER(c.publicId) = LOWER(:categoryId)')
                ->setParameter('categoryId', $getAllProductsRequestDto->getCategoryPublicId()->getPublicId());
        }

        $query->setParameter('search', '%' . $getAllProductsRequestDto->getSearch() . '%')
            ->setParameter('isDefault', true);

        match ($getAllProductsRequestDto->getFilter()) {
            ProductSortFilterCode::PRICE_ASC => $query->orderBy('pv.price', 'ASC'),
            ProductSortFilterCode::PRICE_DESC => $query->orderBy('pv.price', 'DESC'),
            ProductSortFilterCode::NAME_ASC => $query->orderBy('p.name', 'ASC'),
            ProductSortFilterCode::NAME_DESC => $query->orderBy('p.name', 'DESC'),
            ProductSortFilterCode::CREATED_ASC => $query->orderBy('p.createdAt', 'ASC'),
            ProductSortFilterCode::CREATED_DESC => $query->orderBy('p.createdAt', 'DESC'),
            default => $query->orderBy('p.createdAt', 'ASC')
        };

        match ($getAllProductsRequestDto->getProductType()) {
            ProductSortFilterCode::PRODUCTS_WITH_PRICE => $query->andWhere('pv.price IS NOT NULL'),
            ProductSortFilterCode::PRODUCTS_WITHOUT_PRICE => $query->andWhere('pv.price IS NULL'),
            default => null
        };

        $query->setFirstResult(($getAllProductsRequestDto->getPage() - 1) * $getAllProductsRequestDto->getLimit())
            ->setMaxResults($getAllProductsRequestDto->getLimit())
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

        $productIds = array_map(fn(ResponseResumeProductDto $p) => $p->getId(), $products);

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
