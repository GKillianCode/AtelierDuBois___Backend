<?php

namespace App\Mapper\Request;

use App\Dto\Request\Filter\GetAllProductsRequestDto;
use App\Dto\Types\PublicIdDto;
use App\Enum\SortFilter\ProductSortFilterCode;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductRequestMapper
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    public function mapGetAllProductsRequest(Request $request): GetAllProductsRequestDto
    {
        $this->validateParameters($request);

        $filterValue = $request->query->get('filter');
        $productTypeValue = $request->query->get('productType');
        $categoryPublicIdValue = $request->query->get('category');

        return new GetAllProductsRequestDto(
            page: (int) $request->query->get('page', 1),
            limit: min(100, max(1, (int) $request->query->get('limit', 20))),
            search: trim($request->query->get('search', '')),
            filter: $filterValue ? ProductSortFilterCode::tryFrom($filterValue) : ProductSortFilterCode::CREATED_DESC,
            productType: $productTypeValue ? ProductSortFilterCode::tryFrom($productTypeValue) : ProductSortFilterCode::PRODUCTS_ALL,
            categoryPublicId: $categoryPublicIdValue ? new PublicIdDto($categoryPublicIdValue) : null,
        );
    }

    private function validateParameters(Request $request): void
    {
        $constraints = new Assert\Collection([
            'page' => [
                new Assert\Optional([
                    new Assert\Type('numeric'),
                    new Assert\Range(['min' => 1, 'max' => 100000])
                ])
            ],
            'limit' => [
                new Assert\Optional([
                    new Assert\Choice(['choices' => [20, 50, 100], 'message' => 'Limit must be 20, 50 or 100'])
                ])
            ],
            'search' => [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Length(['max' => 255])
                ])
            ],
            'filter' => [
                new Assert\Optional([
                    new Assert\Type('string')
                ])
            ],
            'productType' => [
                new Assert\Optional([
                    new Assert\Type('string')
                ])
            ],
            'category' => [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Regex([
                        'pattern' => '/^[0-9A-Za-z]{22}$/',
                        'message' => 'Category must be a valid UUID base62 format (22 characters)'
                    ])
                ])
            ]
        ]);

        $violations = $this->validator->validate($request->query->all(), $constraints);

        if (count($violations) > 0) {
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
