<?php

namespace App\Serializer\Normalizer\Product;

use App\Dto\Product\ShortProductDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ShortProductDtoNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'title' => $object->title,
            'type' => $object->type->value,
            'category' => [
                'name' => $object->category->name,
                'publicId' => $object->category->publicId->publicId,
            ],
            'unitPrice' => $object->unitPrice->amount,
            'publicId' => $object->publicId->publicId,
            'imageUrl' => $object->mainImage->imageUrl,
            'averageRating' => $object->averageRating,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ShortProductDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ShortProductDto::class => true];
    }
}
