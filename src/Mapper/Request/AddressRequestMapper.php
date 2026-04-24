<?php

namespace App\Mapper\Request;

use App\Dto\User\AddressDto;
use App\Dto\Types\PublicIdDto;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddressRequestMapper
{
    public function __construct(
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
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
            throw new BadRequestException($this->translator->trans('error.invalid_json', ['%detail%' => json_last_error_msg()], 'validators'));
        }


        $addressData = [
            'publicId' => $publicId,
            'street' => $data['street'] ?? null,
            'city' => $data['city'] ?? null,
            'zipcode' => $data['zipcode'] ?? null,
            'isProfessional' => $data['isProfessional'] ?? null,
            'isDefault' => $data['isDefault'] ?? null,
            'companyName' => $data['companyName'] ?? null
        ];

        return $addressData;
    }

    /** @param array<string, mixed> $data */
    private function validateParameters(array $data, bool $isPublicIdRequired): void
    {

        $publicIdConstraints = $isPublicIdRequired
            ? [
                new Assert\NotBlank(['message' => 'address.public_id.required']),
                new Assert\Type('string'),
                new Assert\Regex([
                    'pattern' => '/^[0-9A-Za-z]{22}$/',
                    'message' => 'address.public_id.uuid_format'
                ])
            ]
            : [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Regex([
                        'pattern' => '/^[0-9A-Za-z]{22}$/',
                        'message' => 'address.public_id.uuid_format'
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
                    'message' => 'address.zipcode.regex'
                ])
            ],
            'isProfessional' => [
                new Assert\Type('bool')
            ],
            'isDefault' => [
                new Assert\Type('bool')
            ],
            'companyName' => [
                new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Length(['min' => 2, 'max' => 80]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Za-z0-9 \'.|+\-*=%!?,]+$/',
                        'message' => 'address.company_name.regex'
                    ])
                ])
            ]
        ]);


        $dataToValidate = $data;
        if (($dataToValidate['isProfessional'] ?? null) === false) {
            unset($dataToValidate['companyName']);
        }

        $violations = $this->validator->validate($dataToValidate, $constraints);

        if (\count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }

            throw new BadRequestException($this->translator->trans('error.validation_failed', ['%details%' => implode(', ', $errors)], 'validators'));
        }

        if ($data['isProfessional'] === true && empty($data['companyName'])) {
            throw new BadRequestException($this->translator->trans('address.company_name.required_professional', [], 'validators'));
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
            isDefault: $addressData['isDefault'] ?? null,
            companyName: $addressData['companyName'] ?? null
        );
    }
}
