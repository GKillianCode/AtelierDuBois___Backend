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

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $normalizedVariants = [];
        foreach ($object->getResponseResumeProductVariantDto() as $variant) {
            $normalizedVariants[] = $this->responseResumeProductVariantDtoNormalizer->normalize($variant, $format, $context);
        }

        return [
            'title' => $object->getResponseResumeProductDto()->getTitle(),
            'description' => $object->getDescription(),
            'type' => $object->getResponseResumeProductDto()->getType()->value,
            'category' => $this->responseResumeProductDtoNormalizer->normalize($object->getResponseResumeProductDto(), $format, $context)['category'],
            'unitPrice' => $object->getResponseResumeProductDto()->getUnitPrice()->getAmount(),
            'publicId' => $object->getResponseResumeProductDto()->getPublicId()->getPublicId(),
            'stock' => $object->getStock(),
            'wood' => $object->getWood(),
            'weightInGrams' => $object->getWeightInGrams(),
            'lengthInCentimeters' => $object->getLengthInCentimeters(),
            'widthInCentimeters' => $object->getWidthInCentimeters(),
            'heightInCentimeters' => $object->getHeightInCentimeters(),
            'imageUrls' => $object->getImageUrls(),
            'productCollection' => $normalizedVariants,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ResponseProductDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseProductDto::class => true];
    }
}
