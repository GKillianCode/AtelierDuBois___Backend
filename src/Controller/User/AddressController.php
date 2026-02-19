<?php

namespace App\Controller\User;

use App\Enum\ApiErrorCode;
use App\Util\ValidatorUtil;
use Psr\Log\LoggerInterface;
use App\Response\ApiResponse;
use App\Manager\User\AddressManager;
use App\Service\User\AddressService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AddressController extends AbstractController
{

    public function __construct(
        public readonly AddressService $addressService,
        public readonly ValidatorUtil $validatorUtil,
        public readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly AddressManager $addressManager,
    ) {}

    #[Route('/api/v1/user/address/add', name: 'address_add', methods: ['POST'])]
    public function addAddress(Request $request): Response
    {
        try {
            $this->logger->debug("AddressController::addAddress ENTER");

            $areUserCanAddAddress = $this->addressManager->canUserAddAddress($this->getUser());

            if ($areUserCanAddAddress) {
                $this->addressService->addAddress($request, $this->getUser());
                return ApiResponse::success();
            } else {
                return ApiResponse::error(ApiErrorCode::ADDRESS_LIMIT_REACHED->getUserMessage(), 'Maximum number of addresses reached.', "Vous avez atteint le nombre maximal d'adresses que vous pouvez ajouter.");
            }
            //return ApiResponse::conflict('The provided data is invalid.', null, "Les données fournies sont invalides. Veuillez vérifier les informations et réessayer.");
        } catch (\Exception $e) {
            $this->logger->error("AddressController::addAddress ERROR::" . $e->getMessage());

            return ApiResponse::serverError(
                'An error occurred while adding the address. ' . $e->getMessage(),
            );
        }
    }

    #[Route('/api/v1/user/address/can-add', name: 'address_can_add', methods: ['GET'])]
    public function canUserAddAddress(): Response
    {
        try {
            $this->logger->debug("AddressController::canUserAddAddress ENTER");

            $areUserCanAddAddress = $this->addressManager->canUserAddAddress($this->getUser());

            $this->logger->debug("AddressController::canUserAddAddress EXIT");
            return ApiResponse::success(['canAddAddress' => $areUserCanAddAddress]);
        } catch (\Exception $e) {
            $this->logger->error("AddressController::canUserAddAddress ERROR::" . $e->getMessage());
            return ApiResponse::serverError(
                'An error occurred while checking address addition capability. ' . $e->getMessage()
            );
        }
    }

    #[Route('/api/v1/user/address/all', name: 'address_get_all', methods: ['GET'])]
    public function getAllAddress(): Response
    {
        try {
            $this->logger->debug("AddressController::getAllAddress ENTER");

            $addresses = $this->addressService->getAllAddressesInDto($this->getUser());

            $this->logger->debug("AddressController::getAllAddress EXIT");
            return ApiResponse::success($this->serializer->normalize($addresses));
        } catch (\Exception $e) {
            $this->logger->error("AddressController::getAllAddress ERROR::" . $e->getMessage());
            return ApiResponse::serverError(
                'An error occurred while getting all addresses. ' . $e->getMessage()
            );
        }
    }

    #[Route('/api/v1/user/address/{publicId}', name: 'address_get', methods: ['GET'])]
    public function getAddress(string $publicId): Response
    {
        try {
            $this->logger->debug("AddressController::getAddress ENTER");

            $addressDto = $this->addressService->getAddressInDtoByPublicId($this->getUser(), $publicId);

            if ($addressDto) {
                $this->logger->debug("AddressController::getAddress EXIT");
                return ApiResponse::success($this->serializer->normalize($addressDto));
            }

            return ApiResponse::notFound('Address not found.');
        } catch (\Exception $e) {
            $this->logger->error("AddressController::getAddress ERROR::" . $e->getMessage());
            return ApiResponse::serverError(
                'An error occurred while getting the address. ' . $e->getMessage()
            );
        }
    }

    #[Route('/api/v1/user/address/{publicId}/update', name: 'address_update', methods: ['PUT'])]
    public function updateAddress(string $publicId, Request $request): Response
    {
        try {
            $this->logger->debug("AddressController::updateAddress ENTER");

            $user = $this->getUser();
            $address = $this->addressManager->getAddressByPublicId($user, $publicId);


            if ($address) {
                $this->addressService->updateAddress($request, $user, $publicId);

                $this->logger->debug("AddressController::updateAddress EXIT");
                return ApiResponse::success(['status' => 'Address updated successfully']);
            } else {
                return ApiResponse::notFound('Address not found.');
            }
            //return ApiResponse::conflict('The provided data is invalid.', $violations, "Les données fournies sont invalides. Veuillez vérifier les informations et réessayer.");
        } catch (\Exception $e) {
            $this->logger->error("AddressController::updateAddress ERROR::" . $e->getMessage());
            return ApiResponse::serverError('Error while updating address');
        }
    }

    #[Route('/api/v1/user/address/{publicId}/remove', name: 'address_remove', methods: ['DELETE'])]
    public function removeAddress(string $publicId): Response
    {
        try {
            $user = $this->getUser();

            $this->addressService->deleteAddress($user, $publicId);

            $this->logger->debug("AddressController::removeAddress EXIT 3");
            return ApiResponse::success('Address removed successfully.');
        } catch (\Exception $e) {
            $this->logger->error("AddressController::removeAddress ERROR::" . $e->getMessage());
            return ApiResponse::serverError('Error while removing address');
        }
    }
}
