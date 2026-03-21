<?php

namespace App\DataFixtures;

use App\Dto\Order\OrderItemDto;
use App\Entity\Shipment\Carrier;
use App\Entity\Shipment\OrderStatus;
use App\Enum\ShipmentStatusCode;
use App\Enum\UserType;
use App\Service\Shipment\ShipmentService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public const ORDER_STATUS_REFERENCE = 'order_status';

    public function __construct(
        private readonly ShipmentService $shipmentService,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $this->createCarrier($manager);
        $manager->flush();

        $this->createOrderStatus($manager);
        $manager->flush();

        $this->createOrders($manager);
    }

    private function createCarrier(ObjectManager $manager): void
    {
        $carrier = new Carrier();
        $carrier->setName('Internal');
        $manager->persist($carrier);
    }

    private function createOrderStatus(ObjectManager $manager): void
    {
        foreach (ShipmentStatusCode::cases() as $index => $statusCode) {
            $orderStatus = new OrderStatus();
            $orderStatus->setName($statusCode->getLabel())
                ->setCode($statusCode);
            $manager->persist($orderStatus);
            $this->addReference(self::ORDER_STATUS_REFERENCE . '_' . $index, $orderStatus);
        }
    }

    private function createOrders(ObjectManager $manager): void
    {
        $users = $manager->getRepository(\App\Entity\User\User::class)->findAll();
        $productVariants = $manager->getRepository(\App\Entity\Product\ProductVariant::class)->findAll();

        if (empty($productVariants)) {
            return;
        }

        foreach ($users as $user) {
            if ($user->getUserType() !== UserType::CUSTOMER) {
                continue;
            }

            for ($i = 0; $i < mt_rand(3, 10); $i++) {
                $numProducts = mt_rand(1, 4);
                $selectedKeys = (array) array_rand($productVariants, min($numProducts, count($productVariants)));

                $orderItems = array_map(
                    fn(int $key) => new OrderItemDto(
                        publicId: $productVariants[$key]->getPublicId(),
                        quantity: mt_rand(1, 3),
                    ),
                    $selectedKeys,
                );

                try {
                    $this->shipmentService->purchaseOrder($orderItems, $user);
                } catch (\Throwable) {
                    // Skip orders for users without a default address or missing data
                    continue;
                }
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProductFixtures::class,
        ];
    }
}
