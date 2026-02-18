<?php

namespace App\Controller\User;

use App\Dto\Register\RegisterUserDto;

use App\Enum\ApiErrorCode;
use App\Enum\ErrorCode;
use App\Manager\User\UserManager;
use App\Mapper\Request\RegisterUserRequestMapper;
use App\Response\ApiResponse;
use App\Response\ErrorResponse;
use App\Service\User\UserService;
use App\Util\ValidatorUtil;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Users')]
final class UserController extends AbstractController
{
    public function __construct(
        public readonly SerializerInterface $serializer,
        public readonly LoggerInterface $logger,
        public readonly UserService $userService,
        public readonly ValidatorUtil $validatorUtil,
        private readonly UserManager $userManager,
        private readonly RegisterUserRequestMapper $registerUserRequestMapper
    ) {}

    #[Route('/api/register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        try {
            $this->logger->debug("UserController::register ENTER");

            $registerUserDto = $this->registerUserRequestMapper->registerUserRequest($request);

            $this->userService->registerUser($registerUserDto);
            $this->logger->debug("UserController::register EXIT 2");
            return ApiResponse::success(['status' => 'User registered successfully']);
        } catch (\Exception $e) {
            $this->logger->error("UserController::register ERROR: " . $e->getMessage());
            return ApiResponse::serverError('An unexpected error occurred');
        }
    }
}
