<?php

namespace App\Dto\Product\RequestFilter;

use App\Dto\Types\PublicIdDto;
use App\Enum\SortFilter\ProductSortFilterCode;

class RequestProductFiltersDto
{
    public function __construct(
        public string $search,
        public ProductSortFilterCode $filter,
        public ProductSortFilterCode $productType,
        public ?PublicIdDto $categoryPublicId,
    ) {}
}
