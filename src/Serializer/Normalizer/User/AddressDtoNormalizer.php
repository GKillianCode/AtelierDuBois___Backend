<?php

namespace App\Serializer\Normalizer\User;

use App\Dto\User\AddressDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AddressDtoNormalizer implements NormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        return [
            'publicId' => $object->getPublicId()->getPublicId(),
            'street' => $object->getStreet(),
            'city' => $object->getCity(),
            'zipcode' => $object->getZipcode(),
            'isProfessional' => $object->isProfessional(),
            'isDefault' => $object->isDefault(),
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AddressDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [AddressDto::class => true];
    }
}
