<?php

namespace App\Mapper\Request;

use App\Dto\Order\OrderItemDto;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderRequestMapper
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    /**
     * @throws BadRequestException
     * @return OrderItemDto[]
     */
    public function mapOrderRequest(Request $request): array
    {
        $orderData = $this->extractJsonData($request);
        $this->validateParameters($orderData);

        return $orderData;
    }

    /**
     * @return OrderItemDto[]
     */
    private function extractJsonData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException('Invalid JSON data: ' . json_last_error_msg());
        }

        $orderData = [];

        $data = json_decode($request->getContent(), true);

        for ($i = 0; $i < \count($data); $i++) {
            $currentItem = $data[$i];

            $orderData[] = new OrderItemDto(
                publicId: $currentItem['publicId'] ?? '',
                quantity: $currentItem['quantity'] ?? 0,
            );
        }

        return $orderData;
    }

    /**
     * @param OrderItemDto[] $data
     */
    private function validateParameters(array $data): void
    {
        $constraints = new Assert\Collection([
            'publicId' => [
                new Assert\NotBlank(['message' => 'PublicId is required']),
                new Assert\Type('string'),
                new Assert\Regex([
                    'pattern' => '/^[0-9A-Za-z]{22}$/',
                    'message' => 'PublicId must be a valid UUID base62 format (22 characters)'
                ])
            ],
            'quantity' => [
                new Assert\NotBlank(['message' => 'Quantity is required']),
                new Assert\Type('integer'),
                new Assert\GreaterThanOrEqual([
                    'value' => 1,
                    'message' => 'Quantity must be at least {{ compared_value }}'
                ])
            ]
        ]);

        if (\count($data) === 0) {
            throw new BadRequestException('Request body must contain at least one item');
        }

        foreach ($data as $item) {
            $violations = $this->validator->validate(
                ['publicId' => $item->getPublicId(), 'quantity' => $item->getQuantity()],
                $constraints
            );

            if (\count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
                }

                throw new BadRequestException('Validation failed: ' . implode(', ', $errors));
            }
        }
    }
}
