<?php

namespace App\Dto\Product;

use App\Enum\SortFilterCode;

class RequestFiltersDto
{
    public function __construct(
        public string $search,
        public SortFilterCode $filter,
        public SortFilterCode $productType,
        public ?PublicIdDto $categoryPublicId,
    ) {}
}
