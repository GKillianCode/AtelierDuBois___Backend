<?php

namespace App\Dto\OpenApiModel;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[OA\Schema(
    title: 'ProductOAModel',
)]
class ProductOAModel
{
    public function __construct(
        public string $title,
        public string $description,
        public string $type,
        public CategoryOAModel $category,
        public int $unitPrice,
        public string $publicId,
        public int $stock,
        public string $wood,
        public int $weightInGrams,
        public int $lengthInCentimeters,
        /** @var string[] */
        public array $imagesUrls,
        public int $averageRating,
        #[OA\Property(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ProductVariantResumeOAModel::class))
        )]
        /** @var ProductVariantResumeOAModel[] */
        public array $productCollection,
    ) {}
}
