<?php

namespace App\Enum;

enum ShipmentStatusCode: string
{
    case PENDING = 'PENDING';
    case IN_TRANSIT = 'IN_TRANSIT';
    case OUT_FOR_DELIVERY = 'OUT_FOR_DELIVERY';
    case DELIVERED = 'DELIVERED';
    case FAILED_ATTEMPT = 'FAILED_ATTEMPT';
    case RETURNED = 'RETURNED';
    case CANCELED = 'CANCELED';
    case LOST = 'LOST';
    case DAMAGED = 'DAMAGED';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'En attente de traitement',
            self::IN_TRANSIT => 'En transit',
            self::OUT_FOR_DELIVERY => 'En cours de livraison',
            self::DELIVERED => 'Livrée',
            self::FAILED_ATTEMPT => 'Échec de livraison',
            self::RETURNED => 'Commande retournée',
            self::CANCELED => 'Commande annulée',
            self::LOST => 'Commande perdue',
            self::DAMAGED => 'Commande endommagée',
        };
    }

    public static function getFirstStatus(): self
    {
        return self::PENDING;
    }
}
