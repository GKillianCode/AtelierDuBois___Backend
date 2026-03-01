<?php

namespace App\Serializer\Normalizer\Shipment;

use App\Dto\Response\ResponseShipmentsPreviewDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseShipmentsPreviewDtoNormalizer implements NormalizerInterface
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
        return $data instanceof ResponseShipmentsPreviewDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseShipmentsPreviewDto::class => true];
    }
}
