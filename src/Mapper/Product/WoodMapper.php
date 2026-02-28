<?php

namespace App\Mapper\Product;

use App\Entity\Product\Wood;
use Psr\Log\LoggerInterface;
use App\Dto\Response\ResponseWoodDto;

class WoodMapper
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function toDtoFromEntity(Wood $wood): ResponseWoodDto
    {
        $this->logger->debug("WoodMapper::toDtoFromEntity ENTER");

        $responseWoodDto = new ResponseWoodDto(
            name: $wood->getName(),
        );

        $this->logger->debug("WoodMapper::toDtoFromEntity EXIT");
        return $responseWoodDto;
    }
}
