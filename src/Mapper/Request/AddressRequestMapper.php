<?php

namespace App\Mapper\Request;

use App\Dto\User\AddressDto;
use App\Dto\Types\PublicIdDto;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddressRequestMapper
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    public function mapAddAddressRequest(Request $request): AddressDto
    {
        $addressData = $this->extractJsonData($request);
        $this->validateParameters($addressData, false);

        return $this->createAddressDto($addressData, false);
    }

    public function mapUpdateAddressRequest(Request $request, string $publicId): AddressDto
    {
        $addressData = $this->extractJsonData($request, $publicId);
        $this->validateParameters($addressData, true);

        return $this->createAddressDto($addressData, true);
    }

    /** @return array<string, mixed> */
    private function extractJsonData(Request $request, ?string $publicId = null): array
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestException('Invalid JSON data: ' . json_last_error_msg());
        }


        $addressData = [
            'publicId' => $publicId,
            'street' => $data['street'] ?? null,
            'city' => $data['city'] ?? null,
            'zipcode' => $data['zipcode'] ?? null,
            'isProfessional' => $data['isProfessional'] ?? null,
            'isDefault' => $data['isDefault'] ?? null
        ];

        return $addressData;
    }

    /** @param array<string, mixed> $data */
    private function validateParameters(array $data, bool $isPublicIdRequired): void
    {

        $publicIdConstraints = $isPublicIdRequired
            ? [
                new Assert\NotBlank(['message' => 'PublicId is required for updates']),
                new Assert\Type('string'),
                new Assert\Regex([
                    'pattern' => '/^[0-9A-Za-z]{22}$/',
                    'message' => 'PublicId must be a valid UUID base62 format (22 characters)'
                ])
            ]
            : [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Regex([
                        'pattern' => '/^[0-9A-Za-z]{22}$/',
                        'message' => 'PublicId must be a valid UUID base62 format (22 characters)'
                    ])
                ])
            ];

        $constraints = new Assert\Collection([
            'publicId' => $publicIdConstraints,
            'street' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length(['min' => 2, 'max' => 255])
            ],
            'city' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length(['min' => 2, 'max' => 100])
            ],
            'zipcode' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Regex([
                    'pattern' => '/^[A-Z0-9\s]+$/',
                    'message' => 'Le code postal ne doit contenir que des lettres majuscules, des chiffres et des espaces.'
                ])
            ],
            'isProfessional' => [
                new Assert\Type('bool')
            ],
            'isDefault' => [
                new Assert\Type('bool')
            ],
        ]);


        $violations = $this->validator->validate($data, $constraints);

        if (\count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }

            throw new BadRequestException('Validation failed: ' . implode(', ', $errors));
        }
    }

    /** @param array<string, mixed> $addressData */
    private function createAddressDto(array $addressData, bool $isPublicIdRequired): AddressDto
    {
        $publicId = $isPublicIdRequired ? new PublicIdDto($addressData['publicId']) : null;

        return new AddressDto(
            publicId: $publicId,
            street: strtolower($addressData['street'] ?? ''),
            city: strtolower($addressData['city'] ?? ''),
            zipcode: $addressData['zipcode'] ?? null,
            isProfessional: $addressData['isProfessional'] ?? null,
            isDefault: $addressData['isDefault'] ?? null
        );
    }
}
