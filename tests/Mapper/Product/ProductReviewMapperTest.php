<?php

namespace App\Tests\Mapper\Product;

use App\Dto\Response\ResponseProductReviewDto;
use App\Entity\Product\ProductReview;
use App\Mapper\Product\ProductReviewMapper;
use PHPUnit\Framework\TestCase;

class ProductReviewMapperTest extends TestCase
{
    private ProductReviewMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ProductReviewMapper();
    }

    public function testToDtoFromEntity(): void
    {
        $review = new ProductReview();
        $review->setRating(5)
            ->setComment('Magnifique table, qualité exceptionnelle.');

        $dto = $this->mapper->toDtoFromEntity($review, 'Jean Dupont');

        $this->assertInstanceOf(ResponseProductReviewDto::class, $dto);
        $this->assertSame(5, $dto->rating);
        $this->assertSame('Magnifique table, qualité exceptionnelle.', $dto->comment);
        $this->assertSame('Jean Dupont', $dto->authorName);
        $this->assertInstanceOf(\DateTime::class, $dto->postedAt);
    }

    public function testToDtoFromEntityDifferentRating(): void
    {
        $review = new ProductReview();
        $review->setRating(3)
            ->setComment('Correct, rien de plus.');

        $dto = $this->mapper->toDtoFromEntity($review, 'Marie Martin');

        $this->assertSame(3, $dto->rating);
        $this->assertSame('Correct, rien de plus.', $dto->comment);
        $this->assertSame('Marie Martin', $dto->authorName);
    }

    public function testToDtoFromEntityMinimalRating(): void
    {
        $review = new ProductReview();
        $review->setRating(1)
            ->setComment('Déçu par la livraison.');

        $dto = $this->mapper->toDtoFromEntity($review, 'Paul Bernard');

        $this->assertSame(1, $dto->rating);
        $this->assertSame('Déçu par la livraison.', $dto->comment);
    }
}
