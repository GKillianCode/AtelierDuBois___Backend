<?php

namespace App\Serializer\Normalizer\Product;

use App\Dto\Product\ProductDetailDto;
use App\Dto\Response\ResponseResumeProductDto;
use App\Dto\Response\ResponseResumeProductVariantDto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseResumeProductVariantDtoNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'title' => $object->responseResumeProductDto->title,
            'description' => $object->description,
            'type' => $object->responseResumeProductDto->type->value,
            'category' => [
                'name' => $object->responseResumeProductDto->category->name,
                'publicId' => $object->responseResumeProductDto->category->publicId->publicId,
            ],
            'unitPrice' => $object->responseResumeProductDto->unitPrice->amount,
            'publicId' => $object->responseResumeProductDto->publicId->publicId,
            'stock' => $object->stock,
            'imageUrls' => $object->imageUrls,
            //TODO: 'averageRating' => $object->averageRating,
            'productCollection' => $object->otherProductVariants,
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ResponseResumeProductDto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ResponseResumeProductDto::class => true];
    }
}
