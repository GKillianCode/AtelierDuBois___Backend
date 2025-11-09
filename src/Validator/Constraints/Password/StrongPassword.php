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
    public string $containsPersonalInfoMessage = 'Le mot de passe ne doit pas contenir d\'informations personnelles.';

    public int $minLength = 12;
    public int $minUppercase = 1;
    public int $minLowercase = 1;
    public int $minNumbers = 1;
    public int $minSpecialChars = 1;
    public bool $checkPersonalInfo = true;
    public string $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    public function __construct(
        mixed $options = [],
        ?array $groups = null,
        mixed $payload = null
    ) {
        // Si $options est un array, on l'utilise pour définir les propriétés
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        parent::__construct($options, $groups, $payload);
    }
}
