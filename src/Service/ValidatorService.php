<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidatorService
{
    public function __construct(private ValidatorInterface $validator) {}

    /**
     * Validate an object (entity or DTO) with optional groups
     * @param object $object
     * @param array|null $groups
     * @return ConstraintViolationListInterface
     */
    public function validate(object $object, ?array $groups = null): ConstraintViolationListInterface
    {
        return $this->validator->validate($object, null, $groups);
    }

    /**
     * Check if an object has validation violations
     * @param object $object
     * @param array|null $groups
     * @return bool
     */
    public function hasViolations(object $object, ?array $groups = null): bool
    {
        return count($this->validate($object, $groups)) > 0;
    }

    /**
     * Return violations as a formatted array
     * @param object $object
     * @param array|null $groups
     * @return array
     */
    public function getViolationsAsArray(object $object, ?array $groups = null): array
    {
        $violations = $this->validate($object, $groups);

        if (count($violations) === 0) {
            return [];
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
                'invalidValue' => $violation->getInvalidValue()
            ];
        }

        return $errors;
    }
}
