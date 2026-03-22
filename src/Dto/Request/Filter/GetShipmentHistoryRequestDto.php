<?php

namespace App\Dto\Request\Filter;

use App\Enum\SortFilter\ShipmentHistorySortFilterCode;

class GetShipmentHistoryRequestDto
{
    public function __construct(
        private int $page,
        private int $limit,
        private string $search,
        private ShipmentHistorySortFilterCode $filter,
        private ShipmentHistorySortFilterCode $filterName,
        private ?int $year,
    ) {}

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    public function getFilter(): ShipmentHistorySortFilterCode
    {
        return $this->filter;
    }

    public function getFilterName(): ShipmentHistorySortFilterCode
    {
        return $this->filterName;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }
}
