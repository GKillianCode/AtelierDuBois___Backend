<?php

namespace App\Manager\Product;

use App\Util\PaginationUtil;
use Psr\Log\LoggerInterface;
use App\Mapper\Product\ProductReviewMapper;
use App\Repository\Product\ProductReviewRepository;
use App\Dto\Request\Filter\GetProductReviewsRequestDto;
use App\Dto\Product\RequestFilter\RequestRatingFiltersDto;

class ProductReviewManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly PaginationUtil $paginationUtil,
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly ProductReviewMapper $productReviewMapper,
    ) {}

    /**
     * Retrieves paginated product reviews for a given variant
     *
     * @param int $variantId The product variant ID
     * @param int $page The page number
     * @param int $limit The items per page limit
     * @param RequestRatingFiltersDto $requestRatingFiltersDto The rating filters
     * @return array Array containing reviews and pagination data
     */
    public function getReviewsByVariantId(GetProductReviewsRequestDto $getProductReviewsRequestDto): array
    {
        $this->logger->debug("ProductReviewManager::getReviewsByVariantId ENTER");

        $paginator = $this->productReviewRepository->paginateProductReviews($getProductReviewsRequestDto);

        $reviewsDto = [];

        foreach ($paginator as $review) {
            $user = $review->getUserId();
            if ($user) {
                $author = $this->sanitizeAuthorName($user->getFirstName(), $user->getLastName());
                $reviewsDto[] = $this->productReviewMapper->toDtoFromEntity($review, $author);
            }
        }

        $paginationDataDto = $this->paginationUtil->getMetaPaginationData($paginator, $getProductReviewsRequestDto->getLimit(), $getProductReviewsRequestDto->getPage());

        $this->logger->debug("ProductReviewManager::getReviewsByVariantId EXIT");

        return [
            'reviews' => $reviewsDto,
            'pagination' => $paginationDataDto
        ];
    }

    /**
     * Sanitizes author name by keeping only first letter of last name
     *
     * @param string $firstname The author's first name
     * @param string $lastname The author's last name
     * @return string The sanitized author name
     */
    public function sanitizeAuthorName(string $firstname, string $lastname): string
    {
        $lastnameArray = str_split($lastname, 1);
        $finalFirstname = htmlspecialchars(ucfirst(strtolower(trim($firstname))), ENT_QUOTES, 'UTF-8');
        $finalLastnameInitial = isset($lastnameArray[0]) ? htmlspecialchars(strtoupper($lastnameArray[0]), ENT_QUOTES, 'UTF-8') . "." : "";
        $sanitizedAuthor = htmlspecialchars(trim("{$finalFirstname} {$finalLastnameInitial}"), ENT_QUOTES, 'UTF-8');
        return $sanitizedAuthor;
    }
}
