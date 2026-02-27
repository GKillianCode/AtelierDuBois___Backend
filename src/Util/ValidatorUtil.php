<?php

namespace App\Util;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidatorUtil
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger
    ) {}

    public function validate(object $object, ?array $groups = null): ConstraintViolationListInterface
    {
        $this->logger->debug("ValidatorUtil::validate ENTER");
        $constraintes = $this->validator->validate($object, null, $groups);
        $this->logger->debug("ValidatorUtil::validate EXIT");
        return $constraintes;
    }

    public function hasViolations(object $object, ?array $groups = null): bool
    {
        $this->logger->debug("ValidatorUtil::hasViolations ENTER");
        $violations = $this->validate($object, $groups);
        $asViolations = \count($violations) > 0 ? true : false;
        $this->logger->debug("ValidatorUtil::hasViolations EXIT");
        return $asViolations;
    }

    public function getViolationsAsArray(object $object, ?array $groups = null): array
    {
        $this->logger->debug("ValidatorUtil::getViolationsAsArray ENTER");
        $violations = $this->validate($object, $groups);

        if (\count($violations) === 0) {
            $this->logger->debug("ValidatorUtil::getViolationsAsArray EXIT 1");
            return [];
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        $this->logger->debug("ValidatorUtil::getViolationsAsArray EXIT 2");
        return $errors;
    }
}
