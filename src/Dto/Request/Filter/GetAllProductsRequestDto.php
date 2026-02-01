<?php

namespace App\Dto\Request\Filter;

use App\Dto\Types\PublicIdDto;
use App\Enum\SortFilter\ProductSortFilterCode;

class GetAllProductsRequestDto
{
    public function __construct(
        private int $page,
        private int $limit,
        private string $search,
        private ProductSortFilterCode $filter,
        private ProductSortFilterCode $productType,
        private ?PublicIdDto $categoryPublicId,
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

    public function getFilter(): ProductSortFilterCode
    {
        return $this->filter;
    }

    public function getProductType(): ProductSortFilterCode
    {
        return $this->productType;
    }

    public function getCategoryPublicId(): ?PublicIdDto
    {
        return $this->categoryPublicId;
    }
}
