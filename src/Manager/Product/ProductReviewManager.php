<?php

namespace App\Manager\Product;

use App\Util\PaginationUtil;
use Psr\Log\LoggerInterface;
use App\Mapper\Product\ProductReviewMapper;
use App\Repository\Product\ProductReviewRepository;
use App\Dto\Request\Filter\GetProductReviewsRequestDto;

class ProductReviewManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly PaginationUtil $paginationUtil,
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly ProductReviewMapper $productReviewMapper,
    ) {}

    public function getReviewsByVariantId(GetProductReviewsRequestDto $getProductReviewsRequestDto): array
    {
        $this->logger->debug("ProductReviewManager::getReviewsByVariantId ENTER");

        $paginator = $this->productReviewRepository->paginateProductReviews($getProductReviewsRequestDto);
        $reviewsDto = [];

        foreach ($paginator as $review) {
            $user = $review->getUserId();
            $author = $user
                ? $this->sanitizeAuthorName($user->getFirstName(), $user->getLastName())
                : 'Utilisateur supprimé';
            $reviewsDto[] = $this->productReviewMapper->toDtoFromEntity($review, $author);
        }

        $paginationDataDto = $this->paginationUtil->getMetaPaginationData($paginator, $getProductReviewsRequestDto->getLimit(), $getProductReviewsRequestDto->getPage());

        $this->logger->debug("ProductReviewManager::getReviewsByVariantId EXIT");

        return [
            'reviews' => $reviewsDto,
            'pagination' => $paginationDataDto
        ];
    }

    // Formats as "Firstname L." — exposes only the first initial of the last name for privacy.
    public function sanitizeAuthorName(string $firstname, string $lastname): string
    {
        $lastnameArray = str_split($lastname, 1);
        $finalFirstname = htmlspecialchars(ucfirst(strtolower(trim($firstname))), ENT_QUOTES, 'UTF-8');
        $finalLastnameInitial = isset($lastnameArray[0]) ? htmlspecialchars(strtoupper($lastnameArray[0]), ENT_QUOTES, 'UTF-8') . "." : "";
        $sanitizedAuthor = htmlspecialchars(trim("{$finalFirstname} {$finalLastnameInitial}"), ENT_QUOTES, 'UTF-8');
        return $sanitizedAuthor;
    }
}
