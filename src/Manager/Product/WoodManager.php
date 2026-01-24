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

    public function create(): void {}

    public function update(Wood $wood): void
    {
        $wood->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function delete(Wood $wood): void
    {
        $this->entityManager->remove($wood);
        $this->entityManager->flush();
    }

    use ValidateAndSaveTrait;
}
