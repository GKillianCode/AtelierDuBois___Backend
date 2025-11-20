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
}
