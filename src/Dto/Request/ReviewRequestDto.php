<?php

namespace App\Dto\Request;

class ReviewRequestDto
{
    public function __construct(
        private readonly int $rating,
        private readonly ?string $comment,
        private readonly ?string $productVariantPublicId = null,
    ) {}

    public function getRating(): int
    {
        return $this->rating;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getProductVariantPublicId(): ?string
    {
        return $this->productVariantPublicId;
    }
}
