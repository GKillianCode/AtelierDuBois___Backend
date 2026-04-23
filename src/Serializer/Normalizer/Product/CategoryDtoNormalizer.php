<?php

namespace App\Serializer\Normalizer\Product;

use App\Dto\Types\CategoryDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryDtoNormalizer implements NormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            'name' => $object->getName(),
            'publicId' => $object->getPublicId()->getPublicId(),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CategoryDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [CategoryDto::class => true];
    }
}
