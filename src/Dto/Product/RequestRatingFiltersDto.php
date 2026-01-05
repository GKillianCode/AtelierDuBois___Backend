<?php

namespace App\Dto\Product;

use App\Enum\CommentSortFilterCode;

class RequestRatingFiltersDto
{
    public function __construct(
        public ?CommentSortFilterCode $ratingOrder,
        public ?int $rating,
        public ?CommentSortFilterCode $publicationOrder,
    ) {}
}
