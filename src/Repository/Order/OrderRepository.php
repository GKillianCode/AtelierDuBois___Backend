<?php

namespace App\Repository\Order;

use App\Entity\User\User;
use App\Entity\Order\Order;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Order::class);
        $this->logger = $logger;
    }

    public function paginateOrders(int $page, int $limit, User $user): Paginator
    {
        $this->logger->debug("OrderRepository::paginateOrders ENTER with page: $page, limit: $limit");

        $query = $this->createQueryBuilder('tOrder')
            ->select('tOrder', 'tUser')
            ->leftJoin('tOrder.userId', 'tUser')
            ->where('tOrder.userId = :userId')
            ->setParameter('userId', $user->getId());

        $query->orderBy('tOrder.createdAt', 'DESC');

        $query->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $this->logger->debug("OrderRepository::paginateOrders EXIT");

        return new Paginator($query, true);
    }
}
