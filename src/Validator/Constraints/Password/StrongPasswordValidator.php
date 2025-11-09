<?php

namespace App\Validator\Constraints\Password;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class StrongPasswordValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof StrongPassword) {
            throw new UnexpectedTypeException($constraint, StrongPassword::class);
        }

        if (null === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $password = $value;

        // Vérifier la longueur minimale
        if (strlen($password) < $constraint->minLength) {
            $this->context->buildViolation($constraint->tooShortMessage)
                ->setParameter('{{ minLength }}', (string) $constraint->minLength)
                ->addViolation();
        }

        // Vérifier les majuscules
        $uppercaseCount = preg_match_all('/[A-Z]/', $password);
        if ($uppercaseCount < $constraint->minUppercase) {
            $this->context->buildViolation($constraint->missingUppercaseMessage)
                ->setParameter('{{ minUppercase }}', (string) $constraint->minUppercase)
                ->addViolation();
        }

        // Vérifier les minuscules
        $lowercaseCount = preg_match_all('/[a-z]/', $password);
        if ($lowercaseCount < $constraint->minLowercase) {
            $this->context->buildViolation($constraint->missingLowercaseMessage)
                ->setParameter('{{ minLowercase }}', (string) $constraint->minLowercase)
                ->addViolation();
        }

        // Vérifier les chiffres
        $numberCount = preg_match_all('/[0-9]/', $password);
        if ($numberCount < $constraint->minNumbers) {
            $this->context->buildViolation($constraint->missingNumbersMessage)
                ->setParameter('{{ minNumbers }}', (string) $constraint->minNumbers)
                ->addViolation();
        }

        // Vérifier les caractères spéciaux
        $specialCharsPattern = '/[' . preg_quote($constraint->specialChars, '/') . ']/';
        $specialCharCount = preg_match_all($specialCharsPattern, $password);
        if ($specialCharCount < $constraint->minSpecialChars) {
            $this->context->buildViolation($constraint->missingSpecialCharsMessage)
                ->setParameter('{{ minSpecialChars }}', (string) $constraint->minSpecialChars)
                ->addViolation();
        }

        // Vérifier les informations personnelles (si l'objet User est disponible)
        if ($constraint->checkPersonalInfo && $this->containsPersonalInfo($password)) {
            $this->context->buildViolation($constraint->containsPersonalInfoMessage)
                ->addViolation();
        }
    }

    private function containsPersonalInfo(string $password): bool
    {
        $object = $this->context->getObject();

        if (!$object || !method_exists($object, 'getFirstname') || !method_exists($object, 'getLastname') || !method_exists($object, 'getEmail')) {
            return false;
        }

        $personalInfo = [
            strtolower($object->getFirstname() ?? ''),
            strtolower($object->getLastname() ?? ''),
            strtolower(explode('@', $object->getEmail() ?? '')[0] ?? ''),
        ];

        $lowerPassword = strtolower($password);

        foreach ($personalInfo as $info) {
            if ($info && strlen($info) > 2 && str_contains($lowerPassword, $info)) {
                return true;
            }
        }

        return false;
    }
}
