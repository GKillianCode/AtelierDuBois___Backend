<?php

namespace App\Serializer\Normalizer\User;

use App\Dto\User\AddressDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AddressDtoNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'publicId' => $object->getPublicId()->getPublicId(),
            'street' => $object->getStreet(),
            'city' => $object->getCity(),
            'zipcode' => $object->getZipcode(),
            'isProfessionnal' => $object->isProfessionnal(),
            'isDefault' => $object->isDefault(),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof AddressDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [AddressDto::class => true];
    }
}
