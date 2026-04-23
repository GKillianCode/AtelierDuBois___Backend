<?php

namespace App\Enum;

enum UserType: string
{
    case ADMIN    = 'ADMIN';
    case INTERNAL = 'INTERNAL';
    case CUSTOMER = 'CUSTOMER';
}
