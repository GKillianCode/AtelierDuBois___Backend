<?php

namespace App\Service\Product;

use App\Entity\Product\ProductReview;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ReviewRightsService
{
    public function __construct(
        #[Autowire(env: 'int:REVIEW_ADD_DAYS_LIMIT')]
        private readonly int $addDaysLimit,
        #[Autowire(env: 'int:REVIEW_EDIT_DAYS_LIMIT')]
        private readonly int $editDaysLimit,
        #[Autowire(env: 'int:REVIEW_DELETE_MONTHS_LIMIT')]
        private readonly int $deleteMonthsLimit,
    ) {}

    public function canAddReview(?ProductReview $existingReview, \DateTimeInterface $purchaseDate): bool
    {
        if ($existingReview !== null) {
            return false;
        }

        $daysSincePurchase = (new \DateTimeImmutable())->diff(
            \DateTimeImmutable::createFromInterface($purchaseDate)
        )->days;

        return $daysSincePurchase <= $this->addDaysLimit;
    }

    public function canEditReview(?ProductReview $existingReview): bool
    {
        if ($existingReview === null || $existingReview->isEdited()) {
            return false;
        }

        $daysSincePublication = (new \DateTimeImmutable())->diff(
            \DateTimeImmutable::createFromInterface($existingReview->getCreatedAt())
        )->days;

        return $daysSincePublication <= $this->editDaysLimit;
    }

    public function canDeleteReview(?ProductReview $existingReview): bool
    {
        if ($existingReview === null) {
            return false;
        }

        $diff = (new \DateTimeImmutable())->diff(
            \DateTimeImmutable::createFromInterface($existingReview->getCreatedAt())
        );
        $monthsSincePublication = ($diff->y * 12) + $diff->m;

        return $monthsSincePublication <= $this->deleteMonthsLimit;
    }
}
