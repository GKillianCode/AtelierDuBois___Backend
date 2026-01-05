<?php

namespace App\Enum;

enum CommentSortFilterCode: string
{
    case RATING_AVERAGE_DESC = 'RATING_AVERAGE_DESC';
    case RATING_AVERAGE_ASC = 'RATING_AVERAGE_ASC';
    case RATING_AVERAGE_EQUAL = 'RATING_AVERAGE_EQUAL';

    case POSTED_DESC = 'POSTED_DESC';
    case POSTED_ASC = 'POSTED_ASC';
}
