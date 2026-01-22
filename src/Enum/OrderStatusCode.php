<?php

namespace App\Enum;

enum OrderStatusCode: string
{
    case PENDING = 'PENDING'; // En attente de traitement
    case IN_TRANSIT = 'IN_TRANSIT'; // En transit
    case OUT_FOR_DELIVERY = 'OUT_FOR_DELIVERY'; // En cours de livraison
    case DELIVERED = 'DELIVERED'; // Livré
    case FAILED_ATTEMPT = 'FAILED_ATTEMPT'; // Tentative de livraison échouée
    case RETURNED = 'RETURNED'; // Retourné
    case CANCELED = 'CANCELED'; // Annulé
    case LOST = 'LOST'; // Perdu
    case DAMAGED = 'DAMAGED'; // Endommagé

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
