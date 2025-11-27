<?php

namespace App\Enum;

enum ProductType: string
{
    case IN_STOCK = 'IN_STOCK';
    case CUSTOM_MADE = 'CUSTOM_MADE';
}
