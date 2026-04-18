<?php

namespace App\Mapper\Product;

use App\Dto\Response\ResponseProductReviewDto;


class ProductReviewMapper
{
    public function __construct() {}

    public function toDtoFromEntity($review, $author): ResponseProductReviewDto
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
