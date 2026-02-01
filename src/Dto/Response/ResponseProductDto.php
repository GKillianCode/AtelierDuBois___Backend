<?php

namespace App\Dto\Response;

use App\Dto\Response\ResponseResumeProductDto;

class ResponseProductDto
{
    public function __construct(
        public readonly ResponseResumeProductDto $responseResumeProductDto,
        public readonly string $description,
        public readonly ?int $stock,
        /** @var ImageDto[] */
        public readonly array $imageUrls,
        /** @var ResponseResumeProductVariantDto[] */
        public readonly array $responseResumeProductVariantDto,
    ) {}
}
