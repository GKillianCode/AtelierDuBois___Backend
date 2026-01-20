<?php

namespace App\Service\Order;

use App\Entity\User\User;
use App\Entity\Order\Order;
use App\Service\UuidService;
use Psr\Log\LoggerInterface;
use App\Dto\Order\ShortOrderDto;
use App\Repository\Order\OrderProductRepository;
use App\Service\PaginationService;
use App\Repository\Order\OrderRepository;
use App\Repository\User\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderProductRepository $orderProductRepository,
        private readonly PaginationService $paginationService,
        private readonly UuidService $uuidService,
        private readonly LoggerInterface $logger,
    ) {}

    public function getAllOrders(int $page, int $limit, User $user): array
    {
        $this->logger->debug("OrderService::getAllOrders ENTER");

        $page = max(1, $page);
        $limit = min(80, max(1, $limit));

        $paginator = $this->orderRepository->paginateOrders($page, $limit, $user);

        $ordersDto = $this->getAllOrdersInShortOrderDto($paginator);

        $paginationDataDto = $this->paginationService->getMetaPaginationData($paginator, $limit, $page);

        $this->logger->debug("OrderService::getAllOrders EXIT");

        return [
            'orders' => $ordersDto,
            'pagination' => $paginationDataDto
        ];
    }

    public function getOrderProductsByOrder(Order $order): array
    {
        $this->logger->debug("OrderService::getOrderProducts ENTER");

        $orderProducts = $this->orderProductRepository->getOrderProductsByOrder($order)->getQuery()->getResult();

        $this->logger->debug("OrderService::getOrderProducts EXIT");
        return $orderProducts;
    }

    private function getAllOrdersInShortOrderDto(Paginator $paginator): array
    {
        $this->logger->debug("OrderService::getAllOrdersInShortOrderDto ENTER");

        $products = [];
        foreach ($paginator as $order) {
            $products[] = new ShortOrderDto(
                orderNumber: "ABC",
                trackingNumber: $this->uuidService->generateUuid62(),
                productCount: $this->calculateTotalQuantity($order),
                totalAmount: $this->calculateTotalAmount($order),
                status: $order->getStatusId()->getName(),
                createdAt: $order->getCreatedAt()
            );
        }

        $this->logger->debug("OrderService::getAllOrdersInShortOrderDto EXIT");
        return $products;
    }

    private function calculateTotalQuantity($order): int
    {
        $this->logger->debug("OrderService::calculateTotalQuantity ENTER");

        $totalQuantity = 0;
        foreach ($order->getOrderProducts() as $orderProduct) {
            $totalQuantity += $orderProduct->getQuantity();
        }

        $this->logger->debug("OrderService::calculateTotalQuantity EXIT");
        return $totalQuantity;
    }

    private function calculateTotalAmount($order): int
    {
        $this->logger->debug("OrderService::calculateTotalAmount ENTER");

        $totalAmount = 0;
        foreach ($order->getOrderProducts() as $orderProduct) {
            $totalAmount += $orderProduct->getQuantity() * $orderProduct->getPrice();
        }

        $this->logger->debug("OrderService::calculateTotalAmount EXIT");
        return $totalAmount;
    }
}
