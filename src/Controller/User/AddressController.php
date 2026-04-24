<?php

namespace App\Controller\User;

use App\Dto\OpenApiModel\AddressOAModel;
use App\Entity\User\User;
use App\Enum\ApiErrorCode;
use App\Manager\User\AddressManager;
use App\Response\ApiResponse;
use App\Service\User\AddressService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[OA\Tag(name: 'Users/Addresses')]
final class AddressController extends AbstractController
{
    public function __construct(
        private readonly AddressService $addressService,
        private readonly NormalizerInterface $serializer,
        private readonly AddressManager $addressManager,
    ) {}

    #[Route('/api/v1/user/address/add', name: 'address_add', methods: ['POST'])]
    #[OA\Post(
        summary: 'Add an address for the authenticated user',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Data for the new address',
            content: new OA\JsonContent(ref: new Model(type: AddressOAModel::class))
        ),
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Address added successfully',
    )]
    public function addAddress(Request $request): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $areUserCanAddAddress = $this->addressManager->canUserAddAddress($user);

        if ($areUserCanAddAddress) {
            $this->addressService->addAddress($request, $user);
            return ApiResponse::success();
        } else {
            return ApiResponse::error(
                ApiErrorCode::ADDRESS_LIMIT_REACHED->getUserMessage(),
                'Maximum number of addresses reached.',
                ApiErrorCode::ADDRESS_LIMIT_REACHED->getHttpStatus()
            );
        }
    }

    #[Route('/api/v1/user/address/can-add', name: 'address_can_add', methods: ['GET'])]
    #[OA\Get(
        summary: 'Check if the authenticated user can add an address',
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Address added successfully',
        content: new OA\JsonContent(
            type: 'boolean'
        )
    )]
    public function canUserAddAddress(): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $areUserCanAddAddress = $this->addressManager->canUserAddAddress($user);
        return ApiResponse::success(['canAddAddress' => $areUserCanAddAddress]);
    }


    #[Route('/api/v1/user/address/all', name: 'address_get_all', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get all addresses for the authenticated user',
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Addresses retrieved successfully',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: AddressOAModel::class))
        )
    )]
    public function getAllAddress(): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $addresses = $this->addressService->getAllAddressesInDto($user);
        return ApiResponse::success($this->serializer->normalize($addresses));
    }

    #[Route('/api/v1/user/address/{publicId}', name: 'address_get', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get a specific address by public ID for the authenticated user',
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Address retrieved successfully',
        content: new OA\JsonContent(ref: new Model(type: AddressOAModel::class))
    )]
    public function getAddress(string $publicId): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $addressDto = $this->addressService->getAddressInDtoByPublicId($user, $publicId);

        if ($addressDto) {
            return ApiResponse::success($this->serializer->normalize($addressDto));
        }

        return ApiResponse::notFound('Address not found.');
    }

    #[Route('/api/v1/user/address/{publicId}/update', name: 'address_update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Update a specific address by public ID for the authenticated user',
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Address updated successfully'
    )]
    public function updateAddress(string $publicId, Request $request): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $address = $this->addressManager->getAddressByPublicId($user, $publicId);

        if ($address) {
            $this->addressService->updateAddress($request, $user, $publicId);
            return ApiResponse::success(['status' => 'Address updated successfully']);
        } else {
            return ApiResponse::notFound('Address not found.');
        }
    }

    #[Route('/api/v1/user/address/{publicId}/remove', name: 'address_remove', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Remove a specific address by public ID for the authenticated user',
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Address removed successfully'
    )]
    public function removeAddress(string $publicId): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $this->addressService->deleteAddress($user, $publicId);

        return ApiResponse::success('Address removed successfully.');
    }
}
