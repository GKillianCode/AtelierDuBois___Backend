<?php

namespace App\Validator\Constraints\Password;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class StrongPassword extends Constraint
{
    public string $message = 'Le mot de passe doit contenir au moins {{ minLength }} caractères, incluant au moins {{ minUppercase }} majuscule(s), {{ minLowercase }} minuscule(s), {{ minNumbers }} chiffre(s) et {{ minSpecialChars }} caractère(s) spéciaux.';

    public string $tooShortMessage = 'Le mot de passe doit contenir au moins {{ minLength }} caractères.';
    public string $missingUppercaseMessage = 'Le mot de passe doit contenir au moins {{ minUppercase }} majuscule(s).';
    public string $missingLowercaseMessage = 'Le mot de passe doit contenir au moins {{ minLowercase }} minuscule(s).';
    public string $missingNumbersMessage = 'Le mot de passe doit contenir au moins {{ minNumbers }} chiffre(s).';
    public string $missingSpecialCharsMessage = 'Le mot de passe doit contenir au moins {{ minSpecialChars }} caractère(s) spéciaux.';
    public string $commonPasswordMessage = 'Ce mot de passe est trop commun. Veuillez en choisir un autre.';
    public string $containsPersonalInfoMessage = 'Le mot de passe ne doit pas contenir d\'informations personnelles.';

    public int $minLength = 12;
    public int $minUppercase = 1;
    public int $minLowercase = 1;
    public int $minNumbers = 1;
    public int $minSpecialChars = 1;
    public bool $checkCommonPasswords = true;
    public bool $checkPersonalInfo = true;
    public string $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    public function __construct(
        array $options = [],
        array $groups = null,
        mixed $payload = null,
        int $minLength = null,
        int $minUppercase = null,
        int $minLowercase = null,
        int $minNumbers = null,
        int $minSpecialChars = null,
        bool $checkCommonPasswords = null,
        bool $checkPersonalInfo = null,
        string $specialChars = null,
        string $message = null,
        string $tooShortMessage = null,
        string $missingUppercaseMessage = null,
        string $missingLowercaseMessage = null,
        string $missingNumbersMessage = null,
        string $missingSpecialCharsMessage = null,
        string $commonPasswordMessage = null,
        string $containsPersonalInfoMessage = null
    ) {
        if ($minLength !== null) {
            $this->minLength = $minLength;
        }
        if ($minUppercase !== null) {
            $this->minUppercase = $minUppercase;
        }
        if ($minLowercase !== null) {
            $this->minLowercase = $minLowercase;
        }
        if ($minNumbers !== null) {
            $this->minNumbers = $minNumbers;
        }
        if ($minSpecialChars !== null) {
            $this->minSpecialChars = $minSpecialChars;
        }
        if ($checkCommonPasswords !== null) {
            $this->checkCommonPasswords = $checkCommonPasswords;
        }
        if ($checkPersonalInfo !== null) {
            $this->checkPersonalInfo = $checkPersonalInfo;
        }
        if ($specialChars !== null) {
            $this->specialChars = $specialChars;
        }
        if ($message !== null) {
            $this->message = $message;
        }
        if ($tooShortMessage !== null) {
            $this->tooShortMessage = $tooShortMessage;
        }
        if ($missingUppercaseMessage !== null) {
            $this->missingUppercaseMessage = $missingUppercaseMessage;
        }
        if ($missingLowercaseMessage !== null) {
            $this->missingLowercaseMessage = $missingLowercaseMessage;
        }
        if ($missingNumbersMessage !== null) {
            $this->missingNumbersMessage = $missingNumbersMessage;
        }
        if ($missingSpecialCharsMessage !== null) {
            $this->missingSpecialCharsMessage = $missingSpecialCharsMessage;
        }
        if ($commonPasswordMessage !== null) {
            $this->commonPasswordMessage = $commonPasswordMessage;
        }
        if ($containsPersonalInfoMessage !== null) {
            $this->containsPersonalInfoMessage = $containsPersonalInfoMessage;
        }

        parent::__construct($options, $groups, $payload);
    }
}
