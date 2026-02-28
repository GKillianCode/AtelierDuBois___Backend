<?php

namespace App\Enum;

enum ApiErrorCode: string
{
    case USER_ALREADY_EXISTS = 'USER_ALREADY_EXISTS';
    case ADDRESS_LIMIT_REACHED = 'ADDRESS_LIMIT_REACHED';
    case ADDRESS_ALREADY_EXISTS = 'ADDRESS_ALREADY_EXISTS';
    case ADDRESS_NOT_FOUND = 'ADDRESS_NOT_FOUND';
    case ADDRESS_CANNOT_DELETE_DEFAULT = 'ADDRESS_CANNOT_DELETE_DEFAULT';

    public function getDevMessage(): string
    {
        return match ($this) {
            self::USER_ALREADY_EXISTS => 'User with this email already exists in database',
            self::ADDRESS_LIMIT_REACHED => 'Maximum number of addresses (5) reached for this user',
            self::ADDRESS_ALREADY_EXISTS => 'Address with same coordinates already exists',
            self::ADDRESS_NOT_FOUND => 'Address not found in database',
            self::ADDRESS_CANNOT_DELETE_DEFAULT => 'Cannot delete default address. Set another address as default first',
        };
    }

    public function getUserMessage(): string
    {
        return match ($this) {
            self::USER_ALREADY_EXISTS => 'Un compte existe déjà avec cette adresse email',
            self::ADDRESS_LIMIT_REACHED => 'Vous avez atteint le nombre maximum d\'adresses (5)',
            self::ADDRESS_ALREADY_EXISTS => 'Cette adresse existe déjà dans votre carnet',
            self::ADDRESS_NOT_FOUND => 'L\'adresse demandée est introuvable',
            self::ADDRESS_CANNOT_DELETE_DEFAULT => 'Impossible de supprimer votre adresse par défaut',
        };
    }

    public function getHttpStatus(): int
    {
        return match ($this) {
            self::USER_ALREADY_EXISTS => 409,
            self::ADDRESS_LIMIT_REACHED => 422,
            self::ADDRESS_ALREADY_EXISTS => 409,
            self::ADDRESS_NOT_FOUND => 404,
            self::ADDRESS_CANNOT_DELETE_DEFAULT => 422,
        };
    }
}
