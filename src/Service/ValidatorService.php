<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidatorService
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Validate an object (entity or DTO) with optional groups
     * @param object $object
     * @param array|null $groups
     * @return ConstraintViolationListInterface
     */
    public function validate(object $object, ?array $groups = null): ConstraintViolationListInterface
    {
        $this->logger->debug("ValidatorService::validate ENTER");
        $constraintes = $this->validator->validate($object, null, $groups);
        $this->logger->debug("ValidatorService::validate EXIT");
        return $constraintes;
    }

    /**
     * Check if an object has validation violations
     * @param object $object
     * @param array|null $groups
     * @return bool
     */
    public function hasViolations(object $object, ?array $groups = null): bool
    {
        $this->logger->debug("ValidatorService::hasViolations ENTER");
        $asViolations = !empty($this->validate($object, $groups)) > 0;
        $this->logger->debug("ValidatorService::hasViolations EXIT");
        return $asViolations;
    }

    /**
     * Return violations as a formatted array
     * @param object $object
     * @param array|null $groups
     * @return array
     */
    public function getViolationsAsArray(object $object, ?array $groups = null): array
    {
        $this->logger->debug("ValidatorService::getViolationsAsArray ENTER");
        $violations = $this->validate($object, $groups);

        if (count($violations) === 0) {
            $this->logger->debug("ValidatorService::getViolationsAsArray EXIT 1");
            return [];
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        $this->logger->debug("ValidatorService::getViolationsAsArray EXIT 2");
        return $errors;
    }
}
