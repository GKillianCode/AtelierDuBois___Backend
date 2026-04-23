<?php

namespace App\Mapper\Product;

use App\Dto\Response\ResponseProductReviewDto;
use App\Entity\Product\ProductReview;


class ProductReviewMapper
{
    public function __construct() {}

    public function toDtoFromEntity(ProductReview $review, string $author): ResponseProductReviewDto
    {
        $dto = new ResponseProductReviewDto(
            rating: $review->getRating(),
            comment: $review->getComment(),
            authorName: $author,
            postedAt: $review->getCreatedAt(),
        );

        return $dto;
    }
}
