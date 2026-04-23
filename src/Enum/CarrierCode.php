<?php

namespace App\Enum;

enum CarrierCode: string
{
    case INTERN = 'intern_delivery';
    case LA_POSTE = 'la_poste';
    case DPD = 'dpd';
}
