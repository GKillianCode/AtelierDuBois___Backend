<?php

namespace App\Mapper\Product;

use App\Dto\Response\ResponseProductReviewDto;
use App\Entity\Product\ProductReview;


class ProductReviewMapper
{
    public function __construct() {}

    public function toDtoFromEntity(ProductReview $review, string $author): ResponseProductReviewDto
    {
        $rawComment = $review->getComment();

        $dto = new ResponseProductReviewDto(
            rating: $review->getRating(),
            comment: $rawComment !== null ? htmlspecialchars($rawComment, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : null,
            authorName: $author,
            postedAt: $review->getCreatedAt(),
        );

        return $dto;
    }
}
