<?php

namespace App\Enum\SortFilter;

enum ShipmentHistorySortFilterCode: string
{
    case NAME_DESC = 'NAME_DESC';
    case NAME_ASC = 'NAME_ASC';

    case PRICE_DESC = 'PRICE_DESC';
    case PRICE_ASC = 'PRICE_ASC';

    case ORDERED_DESC = 'ORDERED_DESC';
    case ORDERED_ASC = 'ORDERED_ASC';
}
