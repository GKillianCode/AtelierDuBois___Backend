<?php

namespace App\Enum;

enum SortFilterCode: string
{
    case NAME_DESC = 'NAME_DESC';
    case NAME_ASC = 'NAME_ASC';

    case PRICE_DESC = 'PRICE_DESC';
    case PRICE_ASC = 'PRICE_ASC';

    case CREATED_DESC = 'CREATED_DESC';
    case CREATED_ASC = 'CREATED_ASC';

    case PRODUCTS_ALL = 'PRODUCTS_ALL';
    case PRODUCTS_WITH_PRICE = 'PRODUCTS_WITH_PRICE';
    case PRODUCTS_WITHOUT_PRICE = 'PRODUCTS_WITHOUT_PRICE';
}
