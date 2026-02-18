<?php

namespace App\Dto\Request;

class RegisterUserDto
{
    public function __construct(
        private readonly string $firstname,
        private readonly string $lastname,
        private readonly string $email,
        private readonly string $password,
    ) {}

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
