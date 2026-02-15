<?php

namespace App\Serializer\Normalizer\Product;

use App\Dto\Response\ResponseResumeProductVariantDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseResumeProductVariantDtoNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'publicId' => $object->publicId->publicId,
            'unitPrice' => $object->unitPrice,
            'wood' => $object->wood,
            'imageUrl' => $object->imageUrl,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ResponseResumeProductVariantDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseResumeProductVariantDto::class => true];
    }
}
