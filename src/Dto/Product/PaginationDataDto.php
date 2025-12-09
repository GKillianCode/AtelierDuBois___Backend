<?php

namespace App\Dto\Product;

class PaginationDataDto
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $totalPages,
        public readonly int $totalItems,
        public readonly int $itemsPerPage,
        public readonly bool $hasNextPage,
        public readonly bool $hasPreviousPage
    ) {}
}
