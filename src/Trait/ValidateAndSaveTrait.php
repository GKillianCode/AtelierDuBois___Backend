<?php

namespace App\Trait;

use App\Util\ValidatorUtil;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

trait ValidateAndSaveTrait
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly ValidatorUtil $validatorUtil
    ) {}

    public function validateAndSave(object $entity): void
    {
        $this->logger->debug("ValidateAndSaveTrait::validateAndSave ENTER");
        $violations = $this->validatorUtil->getViolationsAsArray($entity);
        if (!empty($violations)) {
            $this->logger->error("ValidateAndSaveTrait::validateAndSave VALIDATION ERROR");
            throw new \RuntimeException('Validation error while adding address: ' . json_encode($violations));
        }
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->logger->debug("ValidateAndSaveTrait::validateAndSave EXIT");
    }
}
