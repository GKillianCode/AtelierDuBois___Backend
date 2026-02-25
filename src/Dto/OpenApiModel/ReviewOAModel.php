<?php

namespace App\Dto\OpenApiModel;

use OpenApi\Attributes as OA;


#[OA\Schema(
    title: 'ReviewOAModel',
)]
class ReviewOAModel
{
    public function __construct(
        public int $rating,
        public string $authorName,
        public string $comment,
        public \DateTimeInterface $postedAt
    ) {}
}
