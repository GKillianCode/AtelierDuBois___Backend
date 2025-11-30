<?php

namespace App\Service\Product;

use App\Dto\Product\ImageDto;
use App\Entity\Product\Image;

class ImageService
{
    public function imageToImageDto(Image $image): ImageDto
    {
        return new ImageDto($image->getFolderName() . '/' . $image->getImageName() . '.' . $image->getFormat());
    }

    /**
     * @param Image[] $images
     * @return ImageDto[]
     */
    public function imagesToImageDtos(array $images): array
    {
        $imageDtos = [];
        foreach ($images as $image) {
            $imageDtos[] = $this->imageToImageDto($image);
        }
        return $imageDtos;
    }
}
