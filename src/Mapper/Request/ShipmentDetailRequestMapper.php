<?php

namespace App\Mapper\Request;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShipmentDetailRequestMapper
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
    ) {}

    public function mapPublicId(string $publicId): string
    {
        $violations = $this->validator->validate($publicId, [
            new Assert\NotBlank(message: 'public_id.not_blank'),
            new Assert\Regex(
                pattern: '/^SHP-\d{4}-[A-Z0-9]{8}$/',
                message: 'public_id.regex'
            ),
        ]);

        if (\count($violations) > 0) {
            throw new BadRequestException($this->translator->trans((string) $violations[0]->getMessage(), [], 'validators'));
        }

        return $publicId;
    }
}
