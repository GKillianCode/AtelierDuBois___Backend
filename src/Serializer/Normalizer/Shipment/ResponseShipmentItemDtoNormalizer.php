<?php

namespace App\Serializer\Normalizer\Shipment;

use App\Dto\Response\ResponseShipmentItemDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseShipmentItemDtoNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'publicId' => $object->getPublicId(),
            'name' => $object->getName(),
            'quantity' => $object->getQuantity(),
            'mainImage' => $object->getMainImage()->getImageUrl(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ResponseShipmentItemDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseShipmentItemDto::class => true];
    }
}
