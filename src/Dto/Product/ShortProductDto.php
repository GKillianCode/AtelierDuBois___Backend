<?php

namespace App\Dto\Product;

use App\Enum\ProductType;
use App\Dto\Product\CategoryDto;
use Symfony\Component\Validator\Constraints as Assert;

class ShortProductDto
{
    public function __construct(
        public readonly int $id,

        #[Assert\Type(
            type: 'string',
            message: 'La valeur {{ value }} n\'est pas valide pour le titre.'
        )]
        #[Assert\NotBlank(
            message: 'Le titre ne doit pas être vide.'
        )]
        #[Assert\Length(
            min: 2,
            max: 255,
            minMessage: 'Le titre doit contenir au minimum {{ limit }} caractères.',
            maxMessage: 'Le titre ne doit pas dépasser {{ limit }} caractères.'
        )]
        public readonly string $title,

        public readonly ProductType $type,

        public readonly CategoryDto $category,

        public readonly ?PriceDto $unitPrice,

        public readonly ImageDto $mainImage,

        public readonly PublicIdDto $publicId,

        public ?int $averageRating = null
    ) {}
}
