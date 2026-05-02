<?php

namespace App\Serializer\Normalizer\Shipment;

use App\Dto\Response\ResponseProductReviewRightsDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseProductReviewRightsDtoNormalizer implements NormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        assert($object instanceof ResponseProductReviewRightsDto);

        return [
            'productVariantPublicId' => $object->getProductVariantPublicId(),
            'canAdd'                 => $object->canAdd(),
            'canEdit'                => $object->canEdit(),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ResponseProductReviewRightsDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseProductReviewRightsDto::class => true];
    }
}
