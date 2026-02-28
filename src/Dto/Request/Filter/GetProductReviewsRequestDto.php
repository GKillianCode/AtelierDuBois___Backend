<?php

namespace App\Dto\Request\Filter;

use App\Enum\SortFilter\CommentSortFilterCode;

class GetProductReviewsRequestDto
{
    public function __construct(
        private string $productVariantPublicId,
        private int $page,
        private int $limit,
        private ?CommentSortFilterCode $ratingOrder,
        private ?int $rating,
        private ?CommentSortFilterCode $publicationOrder,
    ) {}

    public function getProductVariantPublicId(): string
    {
        return $this->productVariantPublicId;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getRatingOrder(): ?CommentSortFilterCode
    {
        return $this->ratingOrder;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function getPublicationOrder(): ?CommentSortFilterCode
    {
        return $this->publicationOrder;
    }
}
