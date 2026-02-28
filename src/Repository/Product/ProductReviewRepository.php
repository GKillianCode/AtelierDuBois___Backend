<?php

namespace App\Repository\Product;

use Psr\Log\LoggerInterface;
use App\Entity\Product\ProductReview;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Enum\SortFilter\CommentSortFilterCode;
use App\Dto\Request\Filter\GetProductReviewsRequestDto;
use App\Dto\Product\RequestFilter\RequestRatingFiltersDto;
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
    public function paginateProductReviews(GetProductReviewsRequestDto $getProductReviewsRequestDto): Paginator
    {
        $this->logger->debug("ProductReviewRepository::paginateProductReviews ENTER");
        $query = $this->createQueryBuilder('pr')
            ->leftJoin('pr.productVariantId', 'pv')
            ->leftJoin('pr.userId', 'u')
            ->addSelect('u')
            ->where('pv.publicId = :publicId')
            ->setParameter('publicId', $getProductReviewsRequestDto->getProductVariantPublicId());

        switch ($getProductReviewsRequestDto->getRatingOrder()) {
            case CommentSortFilterCode::RATING_AVERAGE_EQUAL:
                $query->andWhere('pr.rating = :rating')
                    ->setParameter('rating', $getProductReviewsRequestDto->getRating());
                break;

            case CommentSortFilterCode::RATING_AVERAGE_ASC:
            case CommentSortFilterCode::RATING_AVERAGE_DESC:
                match ($getProductReviewsRequestDto->getRatingOrder()) {
                    CommentSortFilterCode::RATING_AVERAGE_ASC => $query->orderBy('pr.rating', 'ASC'),
                    CommentSortFilterCode::RATING_AVERAGE_DESC => $query->orderBy('pr.rating', 'DESC'),
                    default => $query->orderBy('pr.rating', 'DESC'),
                };
                break;

            default:
                break;
        }

        match ($getProductReviewsRequestDto->getPublicationOrder()) {
            CommentSortFilterCode::POSTED_ASC => $query->addOrderBy('pr.createdAt', 'ASC'),
            CommentSortFilterCode::POSTED_DESC => $query->addOrderBy('pr.createdAt', 'DESC'),
            default => $query->addOrderBy('pr.createdAt', 'DESC'),
        };

        $query->setFirstResult(($getProductReviewsRequestDto->getPage() - 1) * $getProductReviewsRequestDto->getLimit())
            ->setMaxResults($getProductReviewsRequestDto->getLimit())
            ->getQuery();

        $this->logger->debug("ProductReviewRepository::paginateProductReviews EXIT");

        return new Paginator($query, true);
    }
}
