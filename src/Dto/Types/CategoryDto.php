<?php

namespace App\Dto\Types;

use App\Dto\Types\PublicIdDto;

class CategoryDto
{
    public function __construct(
        private readonly string $name,
        private readonly PublicIdDto $publicId,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getPublicId(): PublicIdDto
    {
        return $this->publicId;
    }
}
