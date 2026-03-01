<?php

namespace App\Manager\Product;

use App\Entity\Product\Image;
use App\Exception\NotFoundException;
use App\Repository\Product\ImageRepository;

class ImageManager
{
    public function __construct(
        private readonly ImageRepository $imageRepository,
    ) {}

    public function getDefaultImageForVariant(int $variantId): Image
    {
        $image = $this->imageRepository->findOneBy(['productVariantId' => $variantId, 'isDefault' => true]);

        if (!$image) {
            throw new NotFoundException('Image', $variantId);
        }

        return $image;
    }
}
