<?php

namespace App\Dto\OpenApiModel;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'RegisterUserOAModel',
)]
class RegisterUserOAModel
{
    public function __construct(
        public string $firstname,
        public string $lastname,
        public string $email,
        public string $password,
        public string $confirmPassword
    ) {}
}
