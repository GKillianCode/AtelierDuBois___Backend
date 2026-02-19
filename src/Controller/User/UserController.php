<?php

namespace App\Controller\User;

use App\Mapper\Request\RegisterUserRequestMapper;
use App\Response\ApiResponse;
use App\Service\User\UserService;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly UserService $userService,
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
