<?php

namespace App\Serializer\Normalizer\Product;

use App\Dto\Types\CategoryDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CategoryDtoNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'name' => $object->getName(),
            'publicId' => $object->getPublicId()->getPublicId(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof CategoryDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [CategoryDto::class => true];
    }
}
