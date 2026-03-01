<?php

namespace App\Manager\Product;

use App\Entity\Product\Image;
use App\Exception\ForbiddenException;
use App\Repository\Product\ImageRepository;

class ImageManager
{
    public function __construct(
        private readonly ImageRepository $imageRepository,
    ) {}

    public function getDefaultImageByProductId(int $productId): ?Image
    {
        $image = $this->imageRepository->findOneBy(['productVariantId' => $productId, 'isDefault' => true]);

        if (!$image) {
            throw new ForbiddenException('No default image found for this product');
        }

        return $image;
    }
}
