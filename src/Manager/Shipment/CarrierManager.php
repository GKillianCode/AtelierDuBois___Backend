<?php

namespace App\Manager\Shipment;

use App\Entity\Shipment\Carrier;
use App\Util\ValidatorUtil;
use Psr\Log\LoggerInterface;
use App\Trait\ValidateAndSaveTrait;
use Doctrine\ORM\EntityManagerInterface;

class CarrierManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorUtil $validatorUtil
    ) {}

    public function create(): void
    {
        $this->logger->debug("CarrierManager::create ENTER");

        $this->logger->debug("CarrierManager::create EXIT");
    }

    public function update(Carrier $carrier): void
    {
        $carrier->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function delete(Carrier $carrier): void
    {
        $this->entityManager->remove($carrier);
        $this->entityManager->flush();
    }

    use ValidateAndSaveTrait;
}
