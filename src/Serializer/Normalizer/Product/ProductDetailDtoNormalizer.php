<?php

namespace App\Serializer\Normalizer\Product;

use App\Dto\Product\ProductDetailDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductDetailDtoNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'title' => $object->shortProduct->title,
            'description' => $object->description,
            'type' => $object->shortProduct->type->value,
            'unitPrice' => $object->shortProduct->unitPrice->amount,
            'publicId' => $object->shortProduct->publicId->publicId,
            'stock' => $object->stock,
            'imageUrls' => $object->imageUrls,
            'productCollection' => $object->otherProductVariants,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ProductDetailDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ProductDetailDto::class => true];
    }
}
