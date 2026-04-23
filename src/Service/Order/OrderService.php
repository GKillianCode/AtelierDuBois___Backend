<?php

namespace App\Service\Order;

use App\Util\UuidUtil;
use App\Entity\User\User;
use App\Entity\Order\Order;
use App\Entity\Order\OrderProduct;
use App\Entity\Product\ProductVariant;
use App\Util\PaginationUtil;
use Psr\Log\LoggerInterface;
use App\Dto\Order\ShortOrderDto;
use App\Dto\Types\PaginationDataDto;
use App\Enum\ShipmentStatusCode;
use App\Manager\Shipment\ShipmentStatusManager;
use App\Repository\Order\OrderRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Repository\Order\OrderProductRepository;
use App\Repository\Shipment\ShipmentItemRepository;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderProductRepository $orderProductRepository,
        private readonly ShipmentItemRepository $shipmentItemRepository,
        private readonly PaginationUtil $paginationUtil,
        private readonly UuidUtil $uuidUtil,
        private readonly LoggerInterface $logger,
        private readonly ShipmentStatusManager $shipmentStatusManager
    ) {}

    public function getRealStockForProductVariant(ProductVariant $productVariant): int
    {
        $stock = $productVariant->getStock();
        $status = $this->shipmentStatusManager->getShipmentStatusByCode(ShipmentStatusCode::PENDING->value);

        $reservedStock = $this->shipmentItemRepository->getReservedStockForProductVariant($productVariant, $status);

        return $stock - $reservedStock;
    }

    /**
     * @return array{orders: ShortOrderDto[], pagination: PaginationDataDto}
     */
    public function getAllOrders(int $page, int $limit, User $user): array
    {
        $this->logger->debug("OrderService::getAllOrders ENTER");

        $page = max(1, $page);
        $limit = min(80, max(1, $limit));

        $paginator = $this->orderRepository->paginateOrders($page, $limit, $user);

        $ordersDto = $this->getAllOrdersInShortOrderDto($paginator);

        $paginationDataDto = $this->paginationUtil->getMetaPaginationData($paginator, $limit, $page);

        $this->logger->debug("OrderService::getAllOrders EXIT");

        return [
            'orders' => $ordersDto,
            'pagination' => $paginationDataDto
        ];
    }

    /** @return OrderProduct[] */
    public function getOrderProductsByOrder(Order $order): array
    {
        $this->logger->debug("OrderService::getOrderProducts ENTER");

        $orderProducts = $this->orderProductRepository->getOrderProductsByOrder($order)->getQuery()->getResult();

        $this->logger->debug("OrderService::getOrderProducts EXIT");
        return $orderProducts;
    }

    /**
     * @param Paginator<Order> $paginator
     * @return ShortOrderDto[]
     */
    private function getAllOrdersInShortOrderDto(Paginator $paginator): array
    {
        $this->logger->debug("OrderService::getAllOrdersInShortOrderDto ENTER");

        $products = [];
        foreach ($paginator as $order) {
            $products[] = new ShortOrderDto(
                orderNumber: "ABC",
                trackingNumber: $this->uuidUtil->generateUuid62(),
                productCount: $this->calculateTotalQuantity($order),
                totalAmount: $this->calculateTotalAmount($order),
                status: $order->getShipments()->first()?->getStatusId()?->getName() ?? '',
                createdAt: $order->getCreatedAt()
            );
        }

        $this->logger->debug("OrderService::getAllOrdersInShortOrderDto EXIT");
        return $products;
    }

    private function calculateTotalQuantity(Order $order): int
    {
        $this->logger->debug("OrderService::calculateTotalQuantity ENTER");

        $totalQuantity = 0;
        /** @var OrderProduct[] $orderProducts */
        $orderProducts = $this->orderProductRepository->getOrderProductsByOrder($order)->getQuery()->getResult();
        foreach ($orderProducts as $orderProduct) {
            $totalQuantity += $orderProduct->getQuantity() ?? 0;
        }

        $this->logger->debug("OrderService::calculateTotalQuantity EXIT");
        return $totalQuantity;
    }

    private function calculateTotalAmount(Order $order): int
    {
        $this->logger->debug("OrderService::calculateTotalAmount ENTER");

        $totalAmount = 0;
        /** @var OrderProduct[] $orderProducts */
        $orderProducts = $this->orderProductRepository->getOrderProductsByOrder($order)->getQuery()->getResult();
        foreach ($orderProducts as $orderProduct) {
            $totalAmount += ($orderProduct->getQuantity() ?? 0) * ($orderProduct->getPrice() ?? 0);
        }

        $this->logger->debug("OrderService::calculateTotalAmount EXIT");
        return $totalAmount;
    }
}
