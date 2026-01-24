<?php

namespace App\Manager\Shipment;

use App\Entity\Shipment\OrderStatus;
use App\Util\ValidatorUtil;
use Psr\Log\LoggerInterface;
use App\Enum\ShipmentStatusCode;
use App\Trait\ValidateAndSaveTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Shipment\OrderStatusRepository;

class ShipmentStatusManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly OrderStatusRepository $shipmentStatusRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorUtil $validatorUtil
    ) {}

    public function create(): void
    {
        $this->logger->debug("ShipmentStatusManager::create ENTER");

        $this->logger->debug("ShipmentStatusManager::create EXIT");
    }

    public function update(OrderStatus $shipmentStatus): void
    {
        $shipmentStatus->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function delete(OrderStatus $shipmentStatus): void
    {
        $this->entityManager->remove($shipmentStatus);
        $this->entityManager->flush();
    }

    use ValidateAndSaveTrait;

    public function getShipmentStatusByCode(ShipmentStatusCode $code): ?OrderStatus
    {
        $this->logger->debug("ShipmentStatusManager::create ENTER");
        $shipmentStatusCode = $this->shipmentStatusRepository->findOneBy(['code' => $code->value]);
        $this->logger->debug("ShipmentStatusManager::create EXIT");
        return $shipmentStatusCode;
    }
}
