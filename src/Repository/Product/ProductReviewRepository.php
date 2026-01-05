<?php

namespace App\Repository\Product;

use Psr\Log\LoggerInterface;
use App\Entity\Product\ProductReview;
use Doctrine\Persistence\ManagerRegistry;
use App\Dto\Product\RequestFilter\RequestRatingFiltersDto;
use App\Enum\SortFilter\CommentSortFilterCode;
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
    public function paginateProductReviews(int $page, int $limit, int $productId, RequestRatingFiltersDto $requestRatingFiltersDto): Paginator
    {
        $this->logger->debug("ProductReviewRepository::paginateProductReviews ENTER with page: $page, limit: $limit, productId: $productId");
        $query = $this->createQueryBuilder('pr')
            ->leftJoin('pr.userId', 'u')
            ->addSelect('u')
            ->where('pr.productVariantId = :productVariantId')
            ->setParameter('productVariantId', $productId);

        switch ($requestRatingFiltersDto->ratingOrder) {
            case CommentSortFilterCode::RATING_AVERAGE_EQUAL:
                $query->andWhere('pr.rating = :rating')
                    ->setParameter('rating', $requestRatingFiltersDto->rating);
                break;

            case CommentSortFilterCode::RATING_AVERAGE_ASC:
            case CommentSortFilterCode::RATING_AVERAGE_DESC:
                match ($requestRatingFiltersDto->ratingOrder) {
                    CommentSortFilterCode::RATING_AVERAGE_ASC => $query->orderBy('pr.rating', 'ASC'),
                    CommentSortFilterCode::RATING_AVERAGE_DESC => $query->orderBy('pr.rating', 'DESC'),
                    default => $query->orderBy('pr.rating', 'DESC'),
                };
                break;

            default:
                break;
        }

        match ($requestRatingFiltersDto->publicationOrder) {
            CommentSortFilterCode::POSTED_ASC => $query->addOrderBy('pr.createdAt', 'ASC'),
            CommentSortFilterCode::POSTED_DESC => $query->addOrderBy('pr.createdAt', 'DESC'),
            default => $query->addOrderBy('pr.createdAt', 'DESC'),
        };

        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $this->logger->debug("ProductReviewRepository::paginateProductReviews EXIT");

        return new Paginator($query, true);
    }
}
