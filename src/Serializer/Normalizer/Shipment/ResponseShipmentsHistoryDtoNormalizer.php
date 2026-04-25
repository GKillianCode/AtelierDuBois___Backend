<?php

namespace App\Serializer\Normalizer\Shipment;

use App\Dto\Response\ResponseShipmentsHistoryDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseShipmentsHistoryDtoNormalizer implements NormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            'shipment' => array_map(fn($item) => [
                'publicId' => $item->getPublicId(),
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'mainImage' => $item->getMainImage()->getImageUrl(),
            ], $object->getShipments()),
            'totalPriceInCents' => $object->getTotalPriceInCents()->getAmount(),
            'orderedAt' => $object->getOrderedAt(),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ResponseShipmentsHistoryDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseShipmentsHistoryDto::class => true];
    }
}
