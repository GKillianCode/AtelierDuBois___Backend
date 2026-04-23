<?php

namespace App\Dto\OpenApiModel;

use App\Dto\OpenApiModel\ResponseShipmentItemOAModel;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'ResponseShipmentsPreviewDto',
)]
class ResponseShipmentsPreviewAOModel
{
    public function __construct(
        /** @var ResponseShipmentItemOAModel[] */
        #[OA\Property(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ResponseShipmentItemOAModel::class))
        )]
        public array $shipments,
        public int $totalPriceInCents
    ) {}
}
