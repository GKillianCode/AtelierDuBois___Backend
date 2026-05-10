<?php

namespace App\Voter;

use App\Entity\Product\ProductReview;

/**
 * Subject passed to ProductReviewVoter.
 * Carries the resolved context needed by ReviewRightsService.
 */
readonly class ReviewVoterSubject
{
    public function __construct(
        public ?ProductReview $existingReview,
        public ?\DateTimeInterface $purchaseDate = null,
    ) {}
}
