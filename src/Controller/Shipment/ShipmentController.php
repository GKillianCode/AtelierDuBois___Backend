<?php

namespace App\Controller\Shipment;

use App\Mapper\Request\ShipmentHistoryRequestMapper;
use App\Response\ApiResponse;
use App\Service\Shipment\ShipmentService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Shipments')]
final class ShipmentController extends AbstractController
{
    public function __construct(
        private readonly ShipmentHistoryRequestMapper $shipmentHistoryRequestMapper,
        private readonly ShipmentService $shipmentService,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/api/v1/shipment/history', name: 'shipment_history', methods: ['POST'])]
    #[OA\Post(
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

        $shipmentHistory = $this->shipmentService->getShipmentHistory($this->getUser(), $getShipmentHistoryRequestDto);
        return ApiResponse::success($this->serializer->normalize($shipmentHistory));
    }
}
