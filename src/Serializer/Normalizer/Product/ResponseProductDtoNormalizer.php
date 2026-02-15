<?php

namespace App\Serializer\Normalizer\Product;

use App\Dto\Response\ResponseProductDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseProductDtoNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ResponseResumeProductDtoNormalizer $responseResumeProductDtoNormalizer,
        private readonly ResponseResumeProductVariantDtoNormalizer $responseResumeProductVariantDtoNormalizer
    ) {}

    public function normalize($object, $format = null, array $context = []): array
    {
        $normalizedVariants = [];
        foreach ($object->responseResumeProductVariantDto as $variant) {
            $normalizedVariants[] = $this->responseResumeProductVariantDtoNormalizer->normalize($variant, $format, $context);
        }

        return [
            'title' => $object->responseResumeProductDto->title,
            'description' => $object->description,
            'type' => $object->responseResumeProductDto->type->value,
            'category' => $this->responseResumeProductDtoNormalizer->normalize($object->responseResumeProductDto, $format, $context)['category'],
            'unitPrice' => $object->responseResumeProductDto->unitPrice->amount,
            'publicId' => $object->responseResumeProductDto->publicId->publicId,
            'stock' => $object->stock,
            'imageUrls' => $object->imageUrls,
            'productCollection' => $normalizedVariants,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ResponseProductDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseProductDto::class => true];
    }
}
