<?php

namespace App\Manager\Product;

use App\Util\ValidatorUtil;
use App\Entity\Product\Wood;
use Psr\Log\LoggerInterface;
use App\Trait\ValidateAndSaveTrait;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Product\WoodRepository;

class WoodManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly WoodRepository $woodRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorUtil $validatorUtil
    ) {}

    public function update(Wood $wood): void
    {
        try {
            $wood->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error updating wood',
                [
                    'exception' => $e->getMessage(),
                    'woodId' => $wood->getId()
                ]
            );
        }
    }

    public function delete(Wood $wood): void
    {
        try {
            $this->entityManager->remove($wood);
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error deleting wood',
                [
                    'exception' => $e->getMessage(),
                    'woodId' => $wood->getId()
                ]
            );
        }
    }

    use ValidateAndSaveTrait;
}
