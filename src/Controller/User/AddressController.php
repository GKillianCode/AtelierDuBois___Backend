<?php

namespace App\Controller\User;

use App\Enum\ErrorCode;
use App\Dto\User\AddressDto;
use App\Response\ErrorResponse;
use App\Service\ValidatorService;
use App\Service\User\AddressService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AddressController extends AbstractController
{

    public function __construct(
        public readonly AddressService $addressService,
        public readonly ValidatorService $validatorService,
        public readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    ) {}

    #[Route('/api/v1/user/address/add', name: 'address_add', methods: ['POST'])]
    public function addAddress(Request $request): Response
    {
        try {
            $this->logger->debug("AddressController::addAddress ENTER");

            $areUserCanAddAddress = $this->addressService->canAddAddress($this->getUser());
            if ($areUserCanAddAddress) {

                $addressDto = $this->serializer->deserialize(
                    $request->getContent(),
                    AddressDto::class,
                    'json'
                );

                $violations = $this->validatorService->getViolationsAsArray($addressDto, null);
                if (empty($violations)) {
                    $user = $this->getUser();
                    $this->addressService->addAddress($addressDto, $user);

                    $this->logger->debug("AddressController::addAddress EXIT");
                    return $this->json([
                        'status' => 'Address registered successfully'
                    ], Response::HTTP_CREATED);
                }

                return $this->createErrorResponse(
                    ErrorCode::INVALID_DATA,
                    'The provided data is invalid.',
                    "Les données fournies sont invalides. Veuillez vérifier les informations et réessayer.",
                    $violations
                );
            }
            return $this->createErrorResponse(
                ErrorCode::ADDRESS_LIMIT_REACHED,
                'Maximum number of addresses reached.',
                "Vous avez atteint le nombre maximal d'adresses que vous pouvez ajouter."
            );
        } catch (\Exception $e) {
            $this->logger->error("AddressController::addAddress ERROR::" . ErrorCode::HTTP_INTERNAL_SERVER_ERROR->value);
            return new JsonResponse(['error' => 'Error while adding address'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/v1/user/address/can-add', name: 'address_can_add', methods: ['GET'])]
    public function canUserAddAddress(): Response
    {
        try {
            $this->logger->debug("AddressController::canUserAddAddress ENTER");

            $areUserCanAddAddress = $this->addressService->canAddAddress($this->getUser());

            $this->logger->debug("AddressController::canUserAddAddress EXIT");
            return $this->json([
                'canAddAddress' => $areUserCanAddAddress
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error("AddressController::canUserAddAddress ERROR::" . ErrorCode::HTTP_INTERNAL_SERVER_ERROR->value);
            return new JsonResponse(['error' => 'Error while checking address addition capability'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/v1/user/address/all', name: 'address_get_all', methods: ['GET'])]
    public function getAllAddress(): Response
    {
        try {
            $this->logger->debug("AddressController::getAllAddress ENTER");

            $addresses = $this->addressService->getAllAddressesInDto($this->getUser());

            $this->logger->debug("AddressController::getAllAddress EXIT");
            return $this->json([
                'addresses' => $addresses
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error("AddressController::getAllAddress ERROR::" . ErrorCode::HTTP_INTERNAL_SERVER_ERROR->value);
            return new JsonResponse(['error' => 'Error while getting all addresses'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
                return $this->json([
                    'address' => $addressDto
                ], Response::HTTP_OK);
            }

            return $this->createErrorResponse(
                ErrorCode::ADDRESS_NOT_FOUND,
                'Address not found.',
                "Adresse non trouvée."
            );
        } catch (\Exception $e) {
            $this->logger->error("AddressController::getAddress ERROR::" . ErrorCode::HTTP_INTERNAL_SERVER_ERROR->value);
            return new JsonResponse(['error' => 'Error while getting address'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createErrorResponse(ErrorCode $code, string $message, string $userMessage, array $details = []): JsonResponse
    {
        $errorResponse = new ErrorResponse($code->value, $message, $details, $userMessage);
        $this->logger->debug("AddressController::addAddress ERROR::" . $code->value);
        return new JsonResponse($errorResponse->toArray(), Response::HTTP_BAD_REQUEST);
    }
}
