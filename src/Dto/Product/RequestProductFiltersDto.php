<?php

namespace App\Dto\Product;

use App\Enum\ProductSortFilterCode;

class RequestProductFiltersDto
{
    public function __construct(
        public string $search,
        public ProductSortFilterCode $filter,
        public ProductSortFilterCode $productType,
        public ?PublicIdDto $categoryPublicId,
    ) {}
}
