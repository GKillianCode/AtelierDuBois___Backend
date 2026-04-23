<?php

namespace App\Dto\Response;

use App\Dto\Response\ResponseResumeProductDto;

class ResponseProductDto
{
    public function __construct(
        private readonly ResponseResumeProductDto $responseResumeProductDto,
        private readonly string $description,
        private readonly ?int $stock,
        private readonly string $wood,
        private readonly int $weightInGrams,
        private readonly int $lengthInCentimeters,
        private readonly int $widthInCentimeters,
        private readonly int $heightInCentimeters,
        /** @var string[] */
        private readonly array $imageUrls,
        /** @var ResponseResumeProductVariantDto[] */
        private readonly array $responseResumeProductVariantDto,
    ) {}

    public function getResponseResumeProductDto(): ResponseResumeProductDto
    {
        return $this->responseResumeProductDto;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function getWood(): string
    {
        return $this->wood;
    }

    public function getWeightInGrams(): int
    {
        return $this->weightInGrams;
    }

    public function getLengthInCentimeters(): int
    {
        return $this->lengthInCentimeters;
    }

    public function getWidthInCentimeters(): int
    {
        return $this->widthInCentimeters;
    }

    public function getHeightInCentimeters(): int
    {
        return $this->heightInCentimeters;
    }

    /**
     * @return string[]
     */
    public function getImageUrls(): array
    {
        return $this->imageUrls;
    }

    /**
     * @return ResponseResumeProductVariantDto[]
     */
    public function getResponseResumeProductVariantDto(): array
    {
        return $this->responseResumeProductVariantDto;
    }
}
