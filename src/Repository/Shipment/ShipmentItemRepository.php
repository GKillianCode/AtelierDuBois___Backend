<?php

namespace App\Repository\Shipment;

use App\Entity\Shipment\OrderStatus;
use Psr\Log\LoggerInterface;
use App\Entity\Shipment\ShipmentItem;
use App\Entity\Product\ProductVariant;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ShipmentItem>
 */
class ShipmentItemRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, ShipmentItem::class);
        $this->logger = $logger;
    }

    public function getReservedStockForProductVariant(ProductVariant $productVariant, OrderStatus $status): int
    {
        $this->logger->debug("ShipmentItemRepository::getReservedStockForProductVariant ENTER");

        $query = $this->createQueryBuilder('tShipmentItem')
            ->select('COALESCE(SUM(tShipmentItem.quantity), 0) as reservedStock')
            ->leftJoin('tShipmentItem.shipmentId', 'tShipment')
            ->leftJoin('tShipment.statusId', 'tStatus')
            ->leftJoin('tShipmentItem.orderProductId', 'tOrderProduct')
            ->where('tOrderProduct.productVariantId = :productVariant')
            ->andWhere('tStatus = :status')
            ->setParameter('productVariant', $productVariant)
            ->setParameter('status', $status);

        $result = $query->getQuery()
            ->getSingleScalarResult();

        $reservedStock = (int) $result;

        $this->logger->debug("ShipmentItemRepository::getReservedStockForProductVariant EXIT");

        return $reservedStock;
    }
}
