<?php

namespace App\Serializer\Normalizer\Product;

use App\Dto\Response\ResponseResumeProductDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseResumeProductDtoNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly CategoryDtoNormalizer $categoryDtoNormalizer
    ) {}

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            'title' => $object->getTitle(),
            'type' => $object->getType()->value,
            'category' => $this->categoryDtoNormalizer->normalize($object->getCategory(), $format, $context),
            'unitPrice' => $object->getUnitPrice() ? $object->getUnitPrice()->getAmount() : null,
            'publicId' => $object->getPublicId()->getPublicId(),
            'imageUrl' => $object->getMainImage()->getImageUrl(),
            'averageRating' => $object->getAverageRating(),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ResponseResumeProductDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseResumeProductDto::class => true];
    }
}
