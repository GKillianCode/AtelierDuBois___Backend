<?php

namespace App\Serializer\Normalizer\User;

use App\Dto\Response\ResponseAddressDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseAddressDtoNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'publicId' => $object->publicId->publicId,
            'street' => $object->street,
            'city' => $object->city,
            'zipcode' => $object->zipcode,
            'isProfessionnal' => $object->isProfessionnal,
            'isDefault' => $object->isDefault,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ResponseAddressDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseAddressDto::class => true];
    }
}
