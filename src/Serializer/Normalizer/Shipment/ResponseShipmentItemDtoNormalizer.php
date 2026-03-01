<?php

namespace App\Serializer\Normalizer\Shipment;

use App\Dto\Response\ResponseShipmentsPreviewDto;
use App\Serializer\Normalizer\Shipment\ResponseShipmentsPreviewDtoNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseShipmentItemDtoNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ResponseShipmentsPreviewDtoNormalizer $responseShipmentsPreviewDtoNormalizer
    ) {}

    public function normalize($object, $format = null, array $context = []): array
    {
        $normalizedItems = [];
        foreach ($object->getShipments() as $shipment) {
            $normalizedItems[] = $this->responseShipmentsPreviewDtoNormalizer->normalize($shipment, $format, $context);
        }

        return [
            'shipments' => $normalizedItems,
            'totalPriceInCents' => $object->getTotalPriceInCents()->getAmount(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ResponseShipmentsPreviewDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseShipmentsPreviewDto::class => true];
    }
}
