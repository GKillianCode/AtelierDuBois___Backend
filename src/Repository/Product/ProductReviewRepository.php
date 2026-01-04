<?php

namespace App\Repository\Product;

use Psr\Log\LoggerInterface;
use App\Entity\Product\ProductReview;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ProductReview>
 */
class ProductReviewRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, ProductReview::class);
        $this->logger = $logger;
    }

    /**
     * Get a paginated list of products based on filters.
     * @param int $page
     * @param int $limit
     * @param int $productVariantId
     * @return Paginator
     */
    public function paginateProductReviews(int $page, int $limit, int $productId): Paginator
    {
        $this->logger->debug("ProductReviewRepository::paginateProductReviews ENTER with page: $page, limit: $limit, productId: $productId");
        $query = $this->createQueryBuilder('pr')
            ->leftJoin('pr.userId', 'u')
            ->addSelect('u')
            ->where('pr.productVariantId = :productVariantId')
            ->setParameter('productVariantId', $productId)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $this->logger->debug("ProductReviewRepository::paginateProductReviews EXIT");

        return new Paginator($query, true);
    }
}
