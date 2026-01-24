<?php

namespace App\Mapper\Product;

use App\Dto\Types\ImageDto;
use Psr\Log\LoggerInterface;
use App\Entity\Product\Image;

class ImageMapper
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function toDtoFromEntity(Image $image): ImageDto
    {
        $this->logger->debug("ImageMapper::toDtoFromEntity ENTER");

        $imageDto = new ImageDto($image->getFolderName() . '/' . $image->getImageName() . '.' . $image->getFormat());

        $this->logger->debug("ImageMapper::toDtoFromEntity EXIT");
        return $imageDto;
    }
}
