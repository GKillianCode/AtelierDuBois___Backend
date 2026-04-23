<?php

namespace App\Serializer\Normalizer\Shipment;

use App\Dto\Response\ResponseShipmentItemDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseShipmentItemDtoNormalizer implements NormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            'publicId' => $object->getPublicId(),
            'name' => $object->getName(),
            'quantity' => $object->getQuantity(),
            'mainImage' => $object->getMainImage()->getImageUrl(),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ResponseShipmentItemDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseShipmentItemDto::class => true];
    }
}
