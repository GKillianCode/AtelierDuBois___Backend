<?php

namespace App\Mapper\Request;

use App\Dto\User\AddressDto;
use App\Dto\Types\PublicIdDto;
use App\Dto\Register\RegisterAddressDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    public function mapUpdateAddressRequest(Request $request): AddressDto
    {
        $addressData = $this->extractJsonData($request);
        $this->validateParameters($addressData, true);

        return $this->createAddressDto($addressData, true);
    }

    private function extractJsonData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestHttpException('Invalid JSON data: ' . json_last_error_msg());
        }

        $addressData = [
            'publicId' => $data['publicId'] ?? null,
            'street' => $data['street'] ?? null,
            'city' => $data['city'] ?? null,
            'zipcode' => $data['zipcode'] ?? null,
            'isProfessionnal' => $data['isProfessionnal'] ?? null,
            'isDefault' => $data['isDefault'] ?? null
        ];

        return $addressData;
    }

    private function validateParameters(array $data, bool $isPublicIdRequired): void
    {

        $publicIdConstraints = $isPublicIdRequired
            ? [
                new Assert\NotBlank(['message' => 'PublicId is required for updates']),
                new Assert\Type('string'),
                new Assert\Regex([
                    'pattern' => '/^[0-9A-Za-z]{20}$/',
                    'message' => 'PublicId must be a valid UUID base62 format (20 characters)'
                ])
            ]
            : [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Regex([
                        'pattern' => '/^[0-9A-Za-z]{20}$/',
                        'message' => 'PublicId must be a valid UUID base62 format (20 characters)'
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
            'isProfessionnal' => [
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
            throw new BadRequestHttpException('Validation failed: ' . implode(', ', $errors));
        }
    }

    private function createAddressDto(array $addressData, bool $isPublicIdRequired): AddressDto
    {
        $publicId = $isPublicIdRequired ? new PublicIdDto($addressData['publicId']) : null;

        return new AddressDto(
            publicId: $publicId,
            street: $addressData['street'] ?? null,
            city: $addressData['city'] ?? null,
            zipcode: $addressData['zipcode'] ?? null,
            isProfessionnal: $addressData['isProfessionnal'] ?? null,
            isDefault: $addressData['isDefault'] ?? null
        );
    }
}
