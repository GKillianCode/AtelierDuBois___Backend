<?php

namespace App\Tests\Service\Product;

use App\Entity\Product\Image;
use PHPUnit\Framework\TestCase;
use App\Service\Product\ImageService;

class ImageServiceTest extends TestCase
{
    private ImageService $imageService;

    protected function setUp(): void
    {
        $this->imageService = new ImageService();
    }

    public function testImageToImageDto(): void
    {
        $imageMock = $this->createMock(Image::class);
        $imageMock->method('getFolderName')->willReturn('folder');
        $imageMock->method('getImageName')->willReturn('image');
        $imageMock->method('getFormat')->willReturn('jpg');

        $imageDto = $this->imageService->imageToImageDto($imageMock);

        $this->assertEquals('folder/image.jpg', $imageDto->getPath());
    }
}
