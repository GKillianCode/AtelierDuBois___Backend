<?php

namespace App\Util;

use Psr\Log\LoggerInterface;
use App\Dto\Types\PaginationDataDto;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationUtil
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @param Paginator<mixed> $paginator
     */
    public function getMetaPaginationData(Paginator $paginator, int $limit, int $page): PaginationDataDto
    {
        $this->logger->debug("PaginationUtil::getMetaPaginationData ENTER");

        $totalItems = \count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        $paginationDataDto = new PaginationDataDto(
            currentPage: $page,
            totalPages: $totalPages,
            totalItems: $totalItems,
            itemsPerPage: $limit,
            hasNextPage: $page < $totalPages,
            hasPreviousPage: $page > 1
        );

        $this->logger->debug("PaginationUtil::getMetaPaginationData EXIT");

        return $paginationDataDto;
    }
}
