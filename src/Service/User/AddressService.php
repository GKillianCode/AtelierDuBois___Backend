<?php

namespace App\Service\User;

use App\Dto\User\AddressDto;
use App\Service\UuidService;

class AddressService
{
    private UuidService $uuidService;

    public function __construct(UuidService $uuidService)
    {
        $this->uuidService = $uuidService;
    }

    public function addAddress(AddressDto $addressDto): void
    {
        $newUuid = $this->uuidService->generateUuid62();
    }

    public function getAddress(int $id): void {}

    public function getAllAddress(): void {}
}
