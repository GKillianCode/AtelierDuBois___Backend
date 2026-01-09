<?php

namespace App\Dto\Product;

class ProductReviewDto
{
    public function __construct(
        public readonly int $averageRating,
        public readonly string $comment,
        public readonly string $authorName,
        public readonly \DateTimeInterface $postedtedAt,
    ) {}
}
