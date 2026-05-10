<?php

namespace App\Manager\Product;

use App\Dto\Request\ReviewRequestDto;
use App\Dto\Types\PaginationDataDto;
use App\Dto\Response\ResponseProductReviewDto;
use App\Entity\Order\Order;
use App\Entity\Product\ProductReview;
use App\Entity\Product\ProductVariant;
use App\Entity\User\User;
use App\Exception\ForbiddenException;
use App\Exception\NotFoundException;
use App\Mapper\Product\ProductReviewMapper;
use App\Repository\Product\ProductReviewRepository;
use App\Repository\Product\ProductVariantRepository;
use App\Service\Product\ReviewRightsService;
use App\Trait\ValidateAndSaveTrait;
use App\Util\PaginationUtil;
use App\Util\ValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use App\Dto\Request\Filter\GetProductReviewsRequestDto;
use Psr\Log\LoggerInterface;

class ProductReviewManager
{
    use ValidateAndSaveTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorUtil $validatorUtil,
        private readonly PaginationUtil $paginationUtil,
        private readonly ProductReviewRepository $productReviewRepository,
        private readonly ProductVariantRepository $productVariantRepository,
        private readonly ProductReviewMapper $productReviewMapper,
        private readonly ReviewRightsService $reviewRightsService,
    ) {}

    /**
     * @return array{reviews: ResponseProductReviewDto[], pagination: PaginationDataDto}
     */
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

    public function addReview(ReviewRequestDto $dto, User $user, Order $order): void
    {
        $variant = $this->productVariantRepository->getProductVariantByPublicId($dto->getProductVariantPublicId());
        if ($variant === null) {
            throw new NotFoundException('ProductVariant', $dto->getProductVariantPublicId());
        }

        $existingReview = $this->productReviewRepository->findByUserVariantAndOrder($user, $variant, $order);

        if (!$this->reviewRightsService->canAddReview($existingReview, $order->getCreatedAt())) {
            throw new ForbiddenException('review.add.forbidden');
        }

        $review = new ProductReview();
        $review->setUserId($user)
            ->setProductVariantId($variant)
            ->setOrderId($order)
            ->setRating($dto->getRating())
            ->setComment($dto->getComment());

        $this->validateAndSave($review);
    }

    public function editReview(ReviewRequestDto $dto, User $user, ProductVariant $variant, Order $order): void
    {
        $review = $this->productReviewRepository->findByUserVariantAndOrder($user, $variant, $order);

        if (!$this->reviewRightsService->canEditReview($review)) {
            throw new ForbiddenException('review.edit.forbidden');
        }

        $review->setRating($dto->getRating())
            ->setComment($dto->getComment())
            ->setIsEdited(true)
            ->setUpdatedAtValue();

        $this->entityManager->flush();
    }

    public function deleteReview(User $user, ProductVariant $variant, Order $order): void
    {
        $review = $this->productReviewRepository->findByUserVariantAndOrder($user, $variant, $order);

        if (!$this->reviewRightsService->canDeleteReview($review)) {
            throw new ForbiddenException('review.delete.forbidden');
        }

        $this->entityManager->remove($review);
        $this->entityManager->flush();
    }

    // Formats as "Firstname L." — exposes only the first initial of the last name for privacy.
    public function sanitizeAuthorName(string $firstname, string $lastname): string
    {
        $lastnameArray = str_split($lastname, 1);
        $finalFirstname = htmlspecialchars(ucfirst(strtolower(trim($firstname))), ENT_QUOTES, 'UTF-8');
        $finalLastnameInitial = isset($lastnameArray[0]) ? htmlspecialchars(strtoupper($lastnameArray[0]), ENT_QUOTES, 'UTF-8') . "." : "";
        return htmlspecialchars(trim("{$finalFirstname} {$finalLastnameInitial}"), ENT_QUOTES, 'UTF-8');
    }
}
