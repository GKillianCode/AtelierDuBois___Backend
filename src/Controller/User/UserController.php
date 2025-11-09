<?php

namespace App\Controller\User;

use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;

use App\Dto\User\RegisterUserDto;
use App\Service\User\UserService;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[OA\Tag(name: 'Users')]
final class UserController extends AbstractController
{

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[OA\Post(
        path: '/api/register',
        summary: 'Inscription d\'un nouvel utilisateur',
        description: 'Créer un compte utilisateur avec validation des données et hachage du mot de passe'
    )]
    #[OA\RequestBody(
        description: 'Données d\'inscription de l\'utilisateur',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: RegisterUserDto::class),
            example: [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'birthDate' => '1990-01-01',
                'email' => 'john.doe@example.com',
                'password' => 'MonMotDePasse123!'
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Utilisateur créé avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'User registered successfully')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Données invalides ou erreur de validation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Le mot de passe doit contenir au moins 12 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.')
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Erreur de validation des contraintes',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'violations', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'propertyPath', type: 'string', example: 'email'),
                        new OA\Property(property: 'message', type: 'string', example: 'Cette adresse email est déjà utilisée.')
                    ]
                ))
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur serveur interne',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'An unexpected error occurred')
            ]
        )
    )]
    #[Route('/api/register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json([
                    'violations' => [
                        ['propertyPath' => '_root', 'message' => 'Invalid JSON']
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            if (isset($data['birthDate']) && is_string($data['birthDate'])) {
                try {
                } catch (\Exception $e) {
                    return $this->json([
                        'violations' => [
                            [
                                'birthDate' => ['La date de naissance doit être au format YYYY-MM-DD']
                            ]
                        ]
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            try {
                $data['birthDate'] = new \DateTimeImmutable($data['birthDate']);
            } catch (\Exception $e) {
                return $this->json([
                    'violations' => [
                        [
                            'birthDate' => ['La date de naissance est invalide (ex: 2025-02-31)']
                        ]
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            $registerUserDto = new RegisterUserDto(
                firstname: $data['firstname'] ?? null,
                lastname: $data['lastname'] ?? null,
                birthDate: $data['birthDate'] ?? null,
                email: $data['email'] ?? null,
                password: $data['password'] ?? null
            );

            $violations = $this->userService->checkValidation($registerUserDto);

            if (!empty($violations)) {
                return $this->json(['violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($this->userService->isUserExistsByEmail($registerUserDto->email)) {
                return $this->json([
                    'violations' => [
                        [
                            'propertyPath' => 'email',
                            'message' => 'Cette adresse email est déjà utilisée.'
                        ]
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->userService->registerUser($registerUserDto);

            return $this->json([
                'status' => 'User registered successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'An unexpected error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
