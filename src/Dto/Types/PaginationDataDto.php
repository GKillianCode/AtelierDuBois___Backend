<?php

namespace App\Dto\Types;

class PaginationDataDto
{
    public function __construct(
        private readonly int $currentPage,
        private readonly int $totalPages,
        private readonly int $totalItems,
        private readonly int $itemsPerPage,
        private readonly bool $hasNextPage,
        private readonly bool $hasPreviousPage
    ) {}

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function hasNextPage(): bool
    {
        return $this->hasNextPage;
    }

    public function hasPreviousPage(): bool
    {
        return $this->hasPreviousPage;
    }
}
