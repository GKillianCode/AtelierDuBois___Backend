<?php

namespace App\Repository\Order;

use App\Entity\Order\Order;
use Psr\Log\LoggerInterface;
use App\Enum\OrderStatusCode;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Order\OrderProduct;
use App\Entity\Order\OrderStatus;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<OrderProduct>
 */
class OrderProductRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, OrderProduct::class);
        $this->logger = $logger;
    }

    public function getOrderProductsByOrder(Order $order): QueryBuilder
    {
        $this->logger->debug("OrderProductRepository::getOrderProductsByOrder ENTER");

        $query = $this->createQueryBuilder('tOrderProduct')
            ->select('tOrderProduct')
            ->where('tOrderProduct.orderId = :orderId')
            ->setParameter('orderId', $order->getId());

        $query->orderBy('tOrderProduct.id', 'DESC');

        $query->getQuery()
            ->getResult();

        $this->logger->debug("OrderProductRepository::getOrderProductsByOrder EXIT");

        return $query;
    }
}
