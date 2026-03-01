<?php

namespace App\Dto\OpenApiModel;

class ResponseShipmentsPreviewAOModel
{
    public function __construct(
        public array $shipments,
        public int $totalPriceInCents
    ) {}
}
