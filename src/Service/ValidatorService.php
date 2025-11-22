<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorService  // ← finit par "Service"
{
    public function __construct(private ValidatorInterface $validator) {}

    public function validateOrJsonError(object $dto): ?JsonResponse
    {
        $violations = $this->validator->validate($dto);

        if (!empty($violations)) {
            return null;
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'champ'  => $violation->getPropertyPath(),
                'erreur' => $violation->getMessage(),
            ];
        }

        return new JsonResponse(['erreurs' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
