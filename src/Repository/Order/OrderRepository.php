<?php

namespace App\Repository\Order;

use App\Dto\Request\Filter\GetShipmentHistoryRequestDto;
use App\Entity\Order\Order;
use App\Entity\User\User;
use App\Enum\SortFilter\ShipmentHistorySortFilterCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function paginateOrders(int $page, int $limit, User $user): Paginator
    {
        $query = $this->createQueryBuilder('tOrder')
            ->select('tOrder')
            ->where('tOrder.userId = :userId')
            ->setParameter('userId', $user->getId());

        $query->orderBy('tOrder.createdAt', 'DESC');

        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new Paginator($query, true);
    }

    public function paginateHistoryOrders(GetShipmentHistoryRequestDto $getShipmentHistoryRequestDto, User $user): Paginator
    {
        $page = $getShipmentHistoryRequestDto->getPage();
        $limit = $getShipmentHistoryRequestDto->getLimit();
        $search = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $getShipmentHistoryRequestDto->getSearch() ?? '');

        $orderBy = match ($getShipmentHistoryRequestDto->getFilter()) {
            ShipmentHistorySortFilterCode::PRICE_ASC    => 'tOrder.totalPrice ASC',
            ShipmentHistorySortFilterCode::PRICE_DESC   => 'tOrder.totalPrice DESC',
            ShipmentHistorySortFilterCode::ORDERED_ASC  => 's.createdAt ASC',
            ShipmentHistorySortFilterCode::ORDERED_DESC => 's.createdAt DESC',
            default                                     => 's.createdAt ASC',
        };

        $dql = "
            SELECT tOrder
            FROM App\Entity\Order\Order tOrder
            INNER JOIN tOrder.shipments s
            INNER JOIN s.shipmentItems si
            INNER JOIN si.orderProductId op
            INNER JOIN op.productVariantId pv
            INNER JOIN pv.productId p
            WHERE tOrder.userId = :userId
            AND (UNACCENT(LOWER(p.name)) LIKE :search)
            ORDER BY {$orderBy}
        ";

        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('userId', $user->getId())
            ->setParameter('search', '%' . $search . '%')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query, true);
    }
}
