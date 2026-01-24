<?php

namespace App\Service\Order;

use App\Util\UuidUtil;
use App\Entity\Order\Order;
use App\Entity\User\Address;
use Psr\Log\LoggerInterface;
use App\Enum\OrderStatusCode;
use App\Entity\Order\Shipment;
use App\Entity\Product\Product;
use App\Entity\Order\OrderProduct;
use App\Entity\Order\ShipmentItem;
use App\Manager\User\AddressManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Order\CarrierRepository;
use App\Repository\Order\OrderStatusRepository;

class ShipmentService
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly UuidUtil $uuidUtil,
        private readonly OrderStatusRepository $orderStatusRepository,
        private readonly CarrierRepository $carrierRepository,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly AddressManager $addressManager
    ) {}

    public function createShipmentsForOrder(Order $order): void
    {
        $orderProducts = $this->orderService->getOrderProductsByOrder($order);
        $user = $order->getUserId();
        $address = $this->addressManager->getDefaultAddressForUser($user);

        foreach ($orderProducts as $orderProduct) {

            $productVariant = $orderProduct->getProductVariantId();
            $product = $productVariant->getProductId();

            $this->createShipments(
                $product,
                $order,
                $address,
                $orderProduct,
                $orderProduct->getQuantity(),
                $product->getMaxStackSize()
            );
        }
    }

    private function createShipments(Product $product, Order $order, Address $address, OrderProduct $orderProduct, int $quantity, int $productMaxStackSize): void
    {
        $quantity = $orderProduct->getQuantity();
        $productMaxStackSize = $product->getMaxStackSize();
        $numberOfShipments = $this->calculateNumberOfShipments($quantity, $productMaxStackSize);
        $realStock = $this->orderService->getRealStockForProductVariant($orderProduct->getProductVariantId());

        $this->createShipment($realStock, $address, $order, $orderProduct, $quantity, $productMaxStackSize, $numberOfShipments);
    }

    private function createShipment(int $realStock, Address $address, Order $order, OrderProduct $orderProduct, int $quantity, int $productMaxStackSize, int $numberOfShipments): void
    {
        for ($i = 0; $i < $numberOfShipments; $i++) {
            $shipment = new Shipment();
            $shipment->setOrderId($order)
                ->setTrackingNumber($this->uuidUtil->generateUuid62())
                ->setOrderNumber($this->generateNewOrderNumber())
                ->setDeliveryAddressId($address)
                ->setBillingAddressId($address)
                ->setCarrierId($this->carrierRepository->findOneBy(['name' => 'INTERNAL']))
                ->setStatusId($this->orderStatusRepository->findOneBy(['code' => OrderStatusCode::getFirstStatus()]));

            $this->entityManager->persist($shipment);
            $this->entityManager->flush();

            $itemsQuantity = $quantity >= $productMaxStackSize ? $productMaxStackSize : $quantity;

            dd($productMaxStackSize . ' - ' . $quantity . ' - ' . $itemsQuantity);

            dump($realStock . ' - ' . $itemsQuantity . ' - ' . $quantity);


            $this->createShipmentItem($shipment, $orderProduct, $itemsQuantity);

            $quantity -= $itemsQuantity;
        }
    }

    private function createShipmentItem(Shipment $shipment, OrderProduct $orderProduct, int $itemsQuantity): void
    {
        $shipmentItem = new ShipmentItem();
        $shipmentItem->setQuantity($itemsQuantity)
            ->setShipmentId($shipment)
            ->setOrderProductId($orderProduct);

        $this->entityManager->persist($shipmentItem);
        $this->entityManager->flush();
    }

    private function calculateNumberOfShipments(int $quantity, int $maxStackSize): int
    {
        return (int) ceil($quantity / $maxStackSize);
    }

    private function generateNewOrderNumber(): string
    {
        $this->logger->debug("ShipmentService::generateNewOrderNumber ENTER");

        $yearMonth = (new \DateTimeImmutable())->format('ym');
        $uuid = $this->uuidUtil->generateUuid62();
        $last8Digits = strtoupper(str_split($uuid, 8)[1]);

        $this->logger->debug("ShipmentService::generateNewOrderNumber EXIT");
        return "ORD-{$yearMonth}-{$last8Digits}";
    }
}
