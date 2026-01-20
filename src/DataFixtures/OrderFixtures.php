<?php

namespace App\DataFixtures;

use App\Enum\UserType;
use DateTimeImmutable;
use App\Entity\Order\Order;
use App\Service\UuidService;
use App\Entity\Order\Carrier;
use App\Enum\OrderStatusCode;
use App\Entity\Order\OrderStatus;
use App\Entity\Order\OrderProduct;
use App\Service\Order\OrderService;
use App\Service\Order\ShipmentService;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public const ORDER_STATUS_REFERENCE = 'order_status';

    public function __construct(
        private readonly UuidService $uuidService,
        private readonly OrderService $orderService,
        private readonly ShipmentService $shipmentService,
    ) {}

    /**
     * Creates sample order statuses and orders with order products
     */
    public function load(ObjectManager $manager): void
    {
        $this->createOrderStatus($manager);
        $manager->flush();

        $this->createOrders($manager);
        $manager->flush();

        $this->createCarrier($manager);
        $manager->flush();
    }

    /**
     * Creates all possible order statuses with French labels
     */
    private function createOrderStatus(ObjectManager $manager): void
    {
        foreach (OrderStatusCode::cases() as $index => $statusCode) {
            $orderStatus = new OrderStatus();
            $orderStatus->setName($statusCode->getLabel())
                ->setCode($statusCode);
            $manager->persist($orderStatus);
            $this->addReference(self::ORDER_STATUS_REFERENCE . '_' . $index, $orderStatus);
        }
    }

    /**
     * Creates sample orders for customer users
     */
    private function createOrders(ObjectManager $manager): void
    {
        $users = $this->getUsersFromDatabase($manager);
        $addresses = $this->getAddressesFromDatabase($manager);
        $orderStatuses = $this->getOrderStatusesFromDatabase($manager);
        $productVariants = $this->getProductVariantsFromDatabase($manager);

        $addressesByUser = [];
        foreach ($addresses as $address) {
            $userId = $address->getUserId()->getId();
            if (!isset($addressesByUser[$userId])) {
                $addressesByUser[$userId] = [];
            }
            $addressesByUser[$userId][] = $address;
        }

        $currentDate = $this->getRandomDateTimeImmuable();

        foreach ($users as $user) {
            $userAddresses = $addressesByUser[$user->getId()] ?? [];
            if (empty($userAddresses)) {
                continue;
            }

            if ($user->getUserType() !== UserType::CUSTOMER) {
                continue;
            }

            $numOrders = 5;
            for ($i = 0; $i < $numOrders; $i++) {
                if (empty($orderStatuses) || empty($productVariants)) {
                    break;
                }

                $order = new Order();
                $order->setUserId($user)
                    ->setUpdatedAt($currentDate);

                $daysToAdd = mt_rand(1, 7);
                $hoursToAdd = mt_rand(1, 23);
                $minutesToAdd = mt_rand(1, 59);
                $currentDate = $currentDate->modify("+{$daysToAdd} days +{$hoursToAdd} hours +{$minutesToAdd} minutes");

                $numProducts = mt_rand(1, 4);
                $selectedVariants = array_rand($productVariants, min($numProducts, count($productVariants)));
                if (!is_array($selectedVariants)) {
                    $selectedVariants = [$selectedVariants];
                }

                $totalPrice = 0;
                foreach ($selectedVariants as $variantIndex) {
                    $variant = $productVariants[$variantIndex];
                    $quantity = mt_rand(1, 3);

                    $orderProduct = new OrderProduct();
                    $orderProduct->setOrderId($order)
                        ->setProductVariantId($variant)
                        ->setPrice($variant->getPrice())
                        ->setQuantity($quantity);

                    $totalPrice += $variant->getPrice() * $quantity;
                    $manager->persist($orderProduct);
                }

                $order->setTotalPrice($totalPrice);
                $manager->persist($order);
                $manager->flush();

                $this->createShipment($order);
            }
        }
    }

    private function createShipment(Order $order): void
    {
        $this->shipmentService->createShipmentsForOrder($order);
    }

    private function createCarrier(ObjectManager $manager): void
    {
        $carrier = new Carrier();
        $carrier->setName('Internal');
        $manager->persist($carrier);
    }

    /**
     * Retrieves users from database
     */
    private function getUsersFromDatabase(ObjectManager $manager): array
    {
        return $manager->getRepository(\App\Entity\User\User::class)->findAll();
    }

    /**
     * Retrieves addresses from database
     */
    private function getAddressesFromDatabase(ObjectManager $manager): array
    {
        return $manager->getRepository(\App\Entity\User\Address::class)->findAll();
    }

    /**
     * Retrieves order statuses from database
     */
    private function getOrderStatusesFromDatabase(ObjectManager $manager): array
    {
        return $manager->getRepository(\App\Entity\Order\OrderStatus::class)->findAll();
    }

    /**
     * Retrieves product variants from database
     */
    private function getProductVariantsFromDatabase(ObjectManager $manager): array
    {
        return $manager->getRepository(\App\Entity\Product\ProductVariant::class)->findAll();
    }

    /**
     * Generates random DateTime for testing purposes
     */
    private function getRandomDateTimeImmuable(): DateTimeImmutable
    {
        $year = 2026;
        $month = mt_rand(10, 12);
        if ($month > 12) {
            $month = 1;
            $year += 1;
        }
        $day = mt_rand(1, 28);
        $hour = mt_rand(9, 22);
        $minute = mt_rand(0, 59);
        $second = mt_rand(0, 59);

        return new DateTimeImmutable(sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second));
    }

    /**
     * Define dependencies on other fixtures
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProductFixtures::class,
        ];
    }
}
