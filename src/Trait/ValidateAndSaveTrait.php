<?php

namespace App\Trait;

trait ValidateAndSaveTrait
{
    public function validateAndSave(object $entity): void
    {
        $this->logger->debug("ValidateAndSaveTrait::validateAndSave ENTER");

        $violations = $this->validatorUtil->getViolationsAsArray($entity);
        if (!empty($violations)) {
            $this->logger->error("ValidateAndSaveTrait::validateAndSave VALIDATION ERROR", [
                'entity' => \get_class($entity),
                'violations' => $violations
            ]);
            throw new \InvalidArgumentException('Validation error: ' . json_encode($violations));
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->logger->debug("ValidateAndSaveTrait::validateAndSave EXIT");
    }
}
