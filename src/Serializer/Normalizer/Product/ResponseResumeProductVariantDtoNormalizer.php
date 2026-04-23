<?php

namespace App\Serializer\Normalizer\Product;

use App\Dto\Response\ResponseResumeProductVariantDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseResumeProductVariantDtoNormalizer implements NormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            'publicId' => $object->getPublicId()->getPublicId(),
            'wood' => $object->getWood(),
            'imageUrl' => $object->getImageUrl(),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ResponseResumeProductVariantDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseResumeProductVariantDto::class => true];
    }
}
