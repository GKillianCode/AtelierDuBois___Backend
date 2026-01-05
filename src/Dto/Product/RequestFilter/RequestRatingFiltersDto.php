<?php

namespace App\Dto\Product\RequestFilter;

use App\Enum\SortFilter\CommentSortFilterCode;

class RequestRatingFiltersDto
{
    public function __construct(
        public ?CommentSortFilterCode $ratingOrder,
        public ?int $rating,
        public ?CommentSortFilterCode $publicationOrder,
    ) {}
}
