<?php

namespace App\Serializer\Normalizer\Shipment;

use App\Dto\Response\ResponseShipmentsHistoryDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseShipmentsHistoryDtoNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'shipments' => array_map(fn($item) => [
                'publicId' => $item->getPublicId(),
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'mainImage' => $item->getMainImage()->getImageUrl(),
            ], $object->getShipments()),
            'totalPriceInCents' => $object->getTotalPriceInCents()->getAmount(),
            'orderedAt' => $object->getOrderedAt(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ResponseShipmentsHistoryDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseShipmentsHistoryDto::class => true];
    }
}
