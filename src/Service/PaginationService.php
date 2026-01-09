<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use App\Dto\Types\PaginationDataDto;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationService
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function getMetaPaginationData(Paginator $paginator, int $limit, int $page): PaginationDataDto
    {
        $this->logger->debug("PaginationService::getMetaPaginationData ENTER");

        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        $paginationDataDto = new PaginationDataDto(
            currentPage: $page,
            totalPages: $totalPages,
            totalItems: $totalItems,
            itemsPerPage: $limit,
            hasNextPage: $page < $totalPages,
            hasPreviousPage: $page > 1
        );

        $this->logger->debug("PaginationService::getMetaPaginationData EXIT");

        return $paginationDataDto;
    }
}
