<?php

namespace App\Dto\Types;

use Symfony\Component\Validator\Constraints as Assert;

class ImageDto
{
    public function __construct(
        #[Assert\Type(
            type: 'string',
            message: 'image.type'
        )]
        #[Assert\NotBlank(
            message: 'image.not_blank'
        )]
        #[Assert\Url(
            requireTld: true,
            message: 'image.url'
        )]
        #[Assert\Length(
            max: 255,
            maxMessage: 'image.max_length'
        )]
        #[Assert\Regex(
            pattern: '/\.webp$/i',
            message: 'image.regex'
        )]
        private readonly string $imageUrl,
    ) {}

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }
}
