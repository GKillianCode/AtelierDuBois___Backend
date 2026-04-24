<?php

namespace App\Dto\Types;

use Symfony\Component\Validator\Constraints as Assert;

class PublicIdDto
{
    public function __construct(
        #[Assert\Type(
            type: 'string',
            message: 'public_id.type'
        )]
        #[Assert\NotBlank(
            message: 'public_id.not_blank'
        )]
        #[Assert\Length(
            min: 22,
            max: 22,
            minMessage: 'public_id.min_length',
            maxMessage: 'public_id.max_length'
        )]
        #[Assert\Regex(
            pattern: '/^[0-9a-zA-Z_-]{22}$/',
            message: 'public_id.regex'
        )]
        private readonly string $publicId
    ) {}

    public function getPublicId(): string
    {
        return $this->publicId;
    }
}
