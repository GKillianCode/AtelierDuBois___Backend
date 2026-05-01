<?php

namespace App\Serializer\Normalizer\Shipment;

use App\Dto\Response\ResponseShipmentDetailDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseShipmentDetailDtoNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly ResponseShipmentItemDtoNormalizer $itemNormalizer,
    ) {}

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        assert($object instanceof ResponseShipmentDetailDto);

        return [
            'shipmentId' => $object->getShipmentId(),
            'orderedAt'  => $object->getOrderedAt(),
            'status'      => [
                'code' => $object->getStatus()->getCode(),
                'name' => $object->getStatus()->getName(),
            ],
            'items' => array_map(
                fn($item) => $this->itemNormalizer->normalize($item, $format, $context),
                $object->getItems(),
            ),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ResponseShipmentDetailDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseShipmentDetailDto::class => true];
    }
}
