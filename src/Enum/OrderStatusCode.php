<?php

namespace App\Enum;

enum OrderStatusCode: string
{
    case PARCEL_CREATED = 'PARCEL_CREATED';
    case PARCEL_BEING_PREPARED = 'PARCEL_BEING_PREPARED';
    case PARCEL_SHIPPED = 'PARCEL_SHIPPED';
    case PARCEL_BEING_DELIVERED = 'PARCEL_BEING_DELIVERED';
    case PARCEL_DELIVERED = 'PARCEL_DELIVERED';
    case PARCEL_NOT_DELIVERED = 'PARCEL_NOT_DELIVERED';
    case PARCEL_RETURNED = 'PARCEL_RETURNED';
    case PARCEL_LOST = 'PARCEL_LOST';
    case PARCEL_CANCELED = 'PARCEL_CANCELED';

    public function getLabel(): string
    {
        return match ($this) {
            self::PARCEL_CREATED => 'Commande créée',
            self::PARCEL_BEING_PREPARED => 'En cours de préparation',
            self::PARCEL_SHIPPED => 'Expédiée',
            self::PARCEL_BEING_DELIVERED => 'En cours de livraison',
            self::PARCEL_DELIVERED => 'Livrée',
            self::PARCEL_NOT_DELIVERED => 'Échec de livraison',
            self::PARCEL_RETURNED => 'Commande retournée',
            self::PARCEL_LOST => 'Commande perdue',
            self::PARCEL_CANCELED => 'Commande annulée',
        };
    }

    public static function getFirstStatus(): self
    {
        return self::PARCEL_CREATED;
    }
}
