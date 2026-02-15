<?php

namespace App\Serializer\Normalizer\Product;

use App\Dto\Response\ResponseResumeProductDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseResumeProductDtoNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly CategoryDtoNormalizer $categoryDtoNormalizer
    ) {}

    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'title' => $object->title,
            'type' => $object->type->value,
            'category' => $this->categoryDtoNormalizer->normalize($object->category, $format, $context),
            'unitPrice' => $object->unitPrice->amount,
            'publicId' => $object->publicId->publicId,
            'imageUrl' => $object->mainImage->imageUrl,
            'averageRating' => $object->averageRating,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ResponseResumeProductDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseResumeProductDto::class => true];
    }
}
