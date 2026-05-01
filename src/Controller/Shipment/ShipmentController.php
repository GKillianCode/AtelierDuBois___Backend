<?php

namespace App\Controller\Shipment;

use App\Dto\Response\ResponseShipmentDetailDto;
use App\Entity\User\User;
use App\Mapper\Request\ShipmentDetailRequestMapper;
use App\Mapper\Request\ShipmentHistoryRequestMapper;
use App\Response\ApiResponse;
use App\Service\Shipment\ShipmentService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[OA\Tag(name: 'Shipments')]
final class ShipmentController extends AbstractController
{
    public function __construct(
        private readonly ShipmentHistoryRequestMapper $shipmentHistoryRequestMapper,
        private readonly ShipmentDetailRequestMapper $shipmentDetailRequestMapper,
        private readonly ShipmentService $shipmentService,
        private readonly NormalizerInterface $serializer,
    ) {}

    #[Route('/api/v1/shipment/history', name: 'shipment_history', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get shipment history',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Shipment history retrieved successfully',
        content: new OA\JsonContent(
            type: 'array',
            //items: new OA\Items(ref: new Model(type: ResponseShipmentItemOAModel::class))
        )
    )]
    public function getHistory(Request $request): Response
    {
        $getShipmentHistoryRequestDto = $this->shipmentHistoryRequestMapper->mapGetAllShipmentsRequest($request);

        $user = $this->getUser();
        assert($user instanceof User);
        $shipmentHistory = $this->shipmentService->getShipmentHistory($user, $getShipmentHistoryRequestDto);
        return ApiResponse::success($this->serializer->normalize($shipmentHistory));
    }

    #[Route('/api/v1/shipment/history/years', name: 'shipment_history_years', methods: ['GET'])]
    #[OA\Get(summary: 'Get distinct years from shipment history')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Distinct years retrieved successfully',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'integer'))
    )]
    public function getHistoryYears(): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $years = $this->shipmentService->getShipmentHistoryYears($user);
        return ApiResponse::success($years);
    }

    #[Route('/api/v1/shipment/history/{publicId}', name: 'shipment_detail', methods: ['GET'])]
    #[OA\Get(summary: 'Get shipment detail by public ID')]
    #[OA\Parameter(name: 'publicId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Shipment detail retrieved successfully',
    )]
    public function getDetail(string $publicId): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        $validatedPublicId = $this->shipmentDetailRequestMapper->mapPublicId($publicId);
        $dto = $this->shipmentService->getShipmentDetail($validatedPublicId, $user);

        return ApiResponse::success($this->serializer->normalize($dto));
    }
}
