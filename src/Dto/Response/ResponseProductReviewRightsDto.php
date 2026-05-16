<?php

namespace App\Dto\Response;

class ResponseProductReviewRightsDto
{
    public function __construct(
        private readonly string $productVariantPublicId,
        private readonly bool $canAdd,
        private readonly bool $canEdit,
    ) {}

    public function getProductVariantPublicId(): string
    {
        return $this->productVariantPublicId;
    }

    public function canAdd(): bool
    {
        return $this->canAdd;
    }

    public function canEdit(): bool
    {
        return $this->canEdit;
    }
}
