<?php

namespace App\Mapper\Request;

use Symfony\Component\HttpFoundation\Request;
use App\Enum\SortFilter\ProductSortFilterCode;
use App\Dto\Request\Filter\GetProductReviewsRequestDto;
use App\Enum\SortFilter\CommentSortFilterCode;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CommentRequestMapper
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    public function mapGetAllCommentsRequest(Request $request, string $publicId): GetProductReviewsRequestDto
    {
        $this->validateParameters($request, $publicId);

        $ratingOrderValue = $request->query->get('ratingOrder');
        $ratingValue = (int) $request->query->get('rating');
        $publicationOrderValue = $request->query->get('publicationOrder');

        return new GetProductReviewsRequestDto(
            productVariantPublicId: $publicId,
            page: (int) $request->query->get('page', 1),
            limit: min(100, max(1, (int) $request->query->get('limit', 20))),
            ratingOrder: $ratingOrderValue ? CommentSortFilterCode::tryFrom($ratingOrderValue) : CommentSortFilterCode::RATING_AVERAGE_DESC,
            rating: $ratingValue >= 1 && $ratingValue <= 5 ? $ratingValue : null,
            publicationOrder: $publicationOrderValue ? CommentSortFilterCode::tryFrom($publicationOrderValue) : CommentSortFilterCode::POSTED_DESC,
        );
    }

    private function validateParameters(Request $request, string $publicId): void
    {
        $data = [...$request->query->all(), 'productVariantPublicId' => $publicId];

        $constraints = new Assert\Collection([
            'productVariantPublicId' => [
                new Assert\Type('string'),
                new Assert\Regex(
                    pattern: '/^[0-9A-Za-z]{20}$/',
                    message: 'Category must be a valid UUID base62 format (20 characters)'
                )
            ],
            'page' => [
                new Assert\Optional([
                    new Assert\Type('numeric'),
                    new Assert\Range(min: 1, max: 100000)
                ])
            ],
            'limit' => [
                new Assert\Optional([
                    new Assert\Choice(choices: [20, 50, 100], message: 'Limit must be 20, 50 or 100')
                ])
            ],
            'ratingOrder' => [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Length(max: 50)
                ])
            ],
            'rating' => [
                new Assert\Optional([
                    new Assert\Type('numeric'),
                    new Assert\Range(min: 1, max: 5)
                ])
            ],
            'publicationOrder' => [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Length(max: 50)
                ])
            ],
        ]);

        $violations = $this->validator->validate($data, $constraints);

        if (\count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            throw new BadRequestException('Validation failed: ' . implode(', ', $errors));
        }

        $this->validateEnumValues($request);
    }

    private function validateEnumValues(Request $request): void
    {
        $filterValue = $request->query->get('filter');
        if ($filterValue && !ProductSortFilterCode::tryFrom($filterValue)) {
            throw new BadRequestException("Invalid filter value: {$filterValue}");
        }

        $productTypeValue = $request->query->get('productType');
        if ($productTypeValue && !ProductSortFilterCode::tryFrom($productTypeValue)) {
            throw new BadRequestException("Invalid productType value: {$productTypeValue}");
        }
    }
}
