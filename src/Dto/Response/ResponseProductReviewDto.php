<?php

namespace App\Dto\Response;

class ResponseProductReviewDto
{
    public function __construct(
        public readonly int $averageRating,
        public readonly string $comment,
        public readonly string $authorName,
        public readonly \DateTimeInterface $postedtedAt,
    ) {}
}
