<?php

namespace App\Mapper\Request;

use App\Dto\Order\OrderItemDto;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderRequestMapper
{
    public function __construct(
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
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
            throw new BadRequestException($this->translator->trans('error.invalid_json', ['%detail%' => json_last_error_msg()], 'validators'));
        }

        $orderData = [];


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
                new Assert\NotBlank(message: 'order.public_id.required'),
                new Assert\Type('string'),
                new Assert\Regex(
                    pattern: '/^[0-9A-Za-z]{22}$/',
                    message: 'order.public_id.uuid_format'
                )
            ],
            'quantity' => [
                new Assert\NotBlank(message: 'order.quantity.required'),
                new Assert\Type('integer'),
                new Assert\GreaterThanOrEqual(
                    value: 1,
                    message: 'order.quantity.min'
                ),
                new Assert\LessThanOrEqual(
                    value: 100,
                    message: 'order.quantity.max'
                ),
            ]
        ]);

        if (\count($data) === 0) {
            throw new BadRequestException($this->translator->trans('order.items.not_empty', [], 'validators'));
        }

        if (\count($data) > 50) {
            throw new BadRequestException($this->translator->trans('order.items.too_many', [], 'validators'));
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

                throw new BadRequestException($this->translator->trans('error.validation_failed', ['%details%' => implode(', ', $errors)], 'validators'));
            }
        }
    }
}
