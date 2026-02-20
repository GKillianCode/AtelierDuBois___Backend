<?php

namespace App\Controller\User;

use App\Mapper\Request\RegisterUserRequestMapper;
use App\Response\ApiResponse;
use App\Service\User\UserService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly RegisterUserRequestMapper $registerUserRequestMapper
    ) {}

    #[Route('/api/register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $registerUserDto = $this->registerUserRequestMapper->registerUserRequest($request);
        $this->userService->registerUser($registerUserDto);

        return ApiResponse::success(['status' => 'User registered successfully']);
    }
}
