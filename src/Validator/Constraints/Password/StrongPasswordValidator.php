<?php

namespace App\Validator\Constraints\Password;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class StrongPasswordValidator extends ConstraintValidator
{
    private array $commonPasswords = [
        'password',
        'password123',
        '123456',
        '123456789',
        'qwerty',
        'abc123',
        'password1',
        'admin',
        'letmein',
        'welcome',
        'monkey',
        '1234567890',
        'dragon',
        'passw0rd',
        'master',
        'hello',
        'freedom',
        'whatever',
        'qazwsx',
        'trustno1',
        'azerty',
        'motdepasse',
        'secret',
        'bonjour',
        'français'
    ];

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof StrongPassword) {
            throw new UnexpectedTypeException($constraint, StrongPassword::class);
        }

        if (null === $value || '' === $value) {
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
            return;
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

        // Vérifier les mots de passe communs
        if ($constraint->checkCommonPasswords && $this->isCommonPassword($password)) {
            $this->context->buildViolation($constraint->commonPasswordMessage)
                ->addViolation();
        }

        // Vérifier les informations personnelles (si l'objet User est disponible)
        if ($constraint->checkPersonalInfo && $this->containsPersonalInfo($password)) {
            $this->context->buildViolation($constraint->containsPersonalInfoMessage)
                ->addViolation();
        }
    }

    private function isCommonPassword(string $password): bool
    {
        $lowerPassword = strtolower($password);

        foreach ($this->commonPasswords as $commonPassword) {
            if (
                $lowerPassword === strtolower($commonPassword) ||
                str_contains($lowerPassword, strtolower($commonPassword))
            ) {
                return true;
            }
        }

        // Vérifier les patterns simples
        if (
            preg_match('/^(.)\1+$/', $password) || // Caractères répétés
            preg_match('/^(012|123|234|345|456|567|678|789|890)+/', $password) || // Séquences numériques
            preg_match('/^(abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz)+/i', $password)
        ) { // Séquences alphabétiques
            return true;
        }

        return false;
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
