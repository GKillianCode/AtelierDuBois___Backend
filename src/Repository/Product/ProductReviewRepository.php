<?php

namespace App\Repository\Product;

use Psr\Log\LoggerInterface;
use App\Entity\Order\Order;
use App\Entity\Product\ProductReview;
use App\Entity\Product\ProductVariant;
use App\Entity\User\User;
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
     * @return Paginator<ProductReview>
     */
    public function paginateProductReviews(GetProductReviewsRequestDto $getProductReviewsRequestDto): Paginator
    {
        $this->logger->debug("ProductReviewRepository::paginateProductReviews ENTER");
        $query = $this->createQueryBuilder('pr')
            ->leftJoin('pr.productVariantId', 'pv')
            ->leftJoin('pr.userId', 'u')
            ->addSelect('u')
            ->where('pv.publicId = :publicId')
            ->setParameter('publicId', $getProductReviewsRequestDto->getProductVariantPublicId()->getPublicId());

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

    public function findByUserAndVariant(User $user, ProductVariant $productVariant): ?ProductReview
    {
        return $this->createQueryBuilder('pr')
            ->where('pr.userId = :user')
            ->andWhere('pr.productVariantId = :variant')
            ->setParameter('user', $user)
            ->setParameter('variant', $productVariant)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUserVariantAndOrder(User $user, ProductVariant $productVariant, Order $order): ?ProductReview
    {
        return $this->createQueryBuilder('pr')
            ->where('pr.userId = :user')
            ->andWhere('pr.productVariantId = :variant')
            ->andWhere('pr.orderId = :order')
            ->setParameter('user', $user)
            ->setParameter('variant', $productVariant)
            ->setParameter('order', $order)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns reviews indexed by productVariantId for a given user + order.
     * Allows bulk rights computation with a single query.
     *
     * @return array<int, ProductReview>
     */
    public function findAllByUserAndOrder(User $user, Order $order): array
    {
        $reviews = $this->createQueryBuilder('pr')
            ->where('pr.userId = :user')
            ->andWhere('pr.orderId = :order')
            ->setParameter('user', $user)
            ->setParameter('order', $order)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($reviews as $review) {
            $indexed[$review->getProductVariantId()->getId()] = $review;
        }

        return $indexed;
    }
}
