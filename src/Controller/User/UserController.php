<?php

namespace App\Controller\User;

use App\Enum\ErrorCode;

use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;
use App\Response\ErrorResponse;
use App\Dto\User\RegisterUserDto;
use App\Service\User\UserService;
use App\Service\ValidatorService;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Users')]
final class UserController extends AbstractController
{
    public function __construct(
        public readonly SerializerInterface $serializer,
        public readonly LoggerInterface $logger,
        public readonly UserService $userService,
        public readonly ValidatorService $validatorService
    ) {}

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
            $this->logger->debug("UserController::register ENTER");

            $registerUserDto = $this->serializer->deserialize(
                $request->getContent(),
                RegisterUserDto::class,
                'json'
            );

            $violations = $this->validatorService->getViolationsAsArray($registerUserDto, ['registration']);
            if (empty($violations)) {
                if ($this->userService->isUserExistsByEmail($registerUserDto->email)) {
                    $this->logger->debug("UserController::register EXIT 1");
                    return $this->createErrorResponse(
                        ErrorCode::USER_ALREADY_EXISTS,
                        'User already exists.',
                        "Cet utilisateur existe déjà.",
                    );
                }

                $this->userService->registerUser($registerUserDto);

                $this->logger->debug("UserController::register EXIT 2");
                return $this->json([
                    'status' => 'User registered successfully'
                ], Response::HTTP_CREATED);
            }

            $this->logger->debug("UserController::register EXIT 3");
            return $this->createErrorResponse(
                ErrorCode::INVALID_DATA,
                'The provided data is invalid.',
                "Les données fournies sont invalides. Veuillez vérifier les informations et réessayer.",
                $violations
            );
        } catch (\Exception $e) {
            $this->logger->error("UserController::register ERROR: " . $e->getMessage());
            return $this->json(['error' => 'An unexpected error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createErrorResponse(ErrorCode $code, string $message, string $userMessage, array $details = []): JsonResponse
    {
        $errorResponse = new ErrorResponse($code->value, $message, $details, $userMessage);
        $this->logger->debug("AddressController::addAddress ERROR::" . $code->value);
        return new JsonResponse($errorResponse->toArray(), Response::HTTP_BAD_REQUEST);
    }
}
