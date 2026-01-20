<?php

namespace App\Service\Order;

use App\Entity\Order\Order;
use App\Service\UuidService;
use Psr\Log\LoggerInterface;
use App\Enum\OrderStatusCode;
use App\Entity\Order\Shipment;
use App\Entity\Product\Product;
use App\Entity\Order\OrderProduct;
use App\Entity\Order\ShipmentItem;
use App\Service\User\AddressService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Order\CarrierRepository;
use App\Repository\Order\OrderStatusRepository;

class ShipmentService
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly UuidService $uuidService,
        private readonly AddressService $addressService,
        private readonly OrderStatusRepository $orderStatusRepository,
        private readonly CarrierRepository $carrierRepository,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function createShipmentsForOrder(Order $order): void
    {
        $orderProducts = $this->orderService->getOrderProductsByOrder($order);
        $user = $order->getUserId();
        $address = $this->addressService->getDefaultAddressForUser($user);

        foreach ($orderProducts as $orderProduct) {

            $productVariant = $orderProduct->getProductVariantId();
            $product = $productVariant->getProductId();

            $this->createShipment(
                $product,
                $order,
                $address,
                $orderProduct,
                $orderProduct->getQuantity(),
                $product->getMaxStackSize()
            );
        }
    }

    private function createShipment(Product $product, Order $order, mixed $address, OrderProduct $orderProduct, int $quantity, int $productMaxStackSize): void
    {
        $quantity = $orderProduct->getQuantity();
        $productMaxStackSize = $product->getMaxStackSize();
        $numberOfShipments = $this->calculateNumberOfShipments($quantity, $productMaxStackSize);

        for ($i = 0; $i < $numberOfShipments; $i++) {
            $shipment = new Shipment();
            $shipment->setOrderId($order)
                ->setTrackingNumber($this->uuidService->generateUuid62())
                ->setOrderNumber($this->generateNewOrderNumber())
                ->setDeliveryAddressId($address)
                ->setBillingAddressId($address)
                ->setCarrierId($this->carrierRepository->findOneBy(['name' => 'INTERNAL']))
                ->setStatusId($this->orderStatusRepository->findOneBy(['code' => OrderStatusCode::getFirstStatus()]));

            $this->entityManager->persist($shipment);
            $this->entityManager->flush();


            $itemsQuantity = $quantity >= $productMaxStackSize ? $productMaxStackSize : $quantity;

            $shipmentItem = new ShipmentItem();
            $shipmentItem->setQuantity($itemsQuantity)
                ->setShipmentId($shipment)
                ->setOrderProductId($orderProduct);

            $this->entityManager->persist($shipmentItem);
            $this->entityManager->flush();

            $quantity -= $itemsQuantity;
        }
    }

    private function calculateNumberOfShipments(int $quantity, int $maxStackSize): int
    {
        return (int) ceil($quantity / $maxStackSize);
    }

    private function generateNewOrderNumber(): string
    {
        $this->logger->debug("ShipmentService::generateNewOrderNumber ENTER");

        $yearMonth = (new \DateTimeImmutable())->format('ym');
        $uuid = $this->uuidService->generateUuid62();
        $last8Digits = strtoupper(str_split($uuid, 8)[1]);

        $this->logger->debug("ShipmentService::generateNewOrderNumber EXIT");
        return "ORD-{$yearMonth}-{$last8Digits}";
    }
}
