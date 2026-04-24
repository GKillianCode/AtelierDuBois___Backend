<?php

namespace App\Serializer\Normalizer\Shipment;

use App\Dto\Response\ResponseShipmentsPreviewDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseShipmentsPreviewDtoNormalizer implements NormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            'shipments' => array_map(static fn($item) => [
                'publicId' => $item->getPublicId(),
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'mainImage' => $item->getMainImage()->getImageUrl(),
            ], $object->getShipments()),
            'totalPriceInCents' => $object->getTotalPriceInCents()->getAmount(),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ResponseShipmentsPreviewDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseShipmentsPreviewDto::class => true];
    }
}
