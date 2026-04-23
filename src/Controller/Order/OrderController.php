<?php

namespace App\Controller\Order;

use App\Dto\OpenApiModel\ResponseShipmentItemOAModel;
use App\Dto\OpenApiModel\ResponseShipmentsPreviewAOModel;
use App\Entity\User\User;
use App\Mapper\Request\OrderRequestMapper;
use App\Response\ApiResponse;
use App\Service\Shipment\ShipmentService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[OA\Tag(name: 'Orders')]
final class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRequestMapper $orderRequestMapper,
        private readonly ShipmentService $shipmentService,
        private readonly NormalizerInterface $serializer,
    ) {}

    #[Route('/api/v1/order/basket', name: 'app_order_basket', methods: ['POST'])]
    #[OA\Post(
        summary: 'Get order basket',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order basket retrieved successfully',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ResponseShipmentItemOAModel::class))
        )
    )]
    public function getOrderBasket(Request $request): Response
    {
        $orderDto = $this->orderRequestMapper->mapOrderRequest($request);
        $basketPreview = $this->shipmentService->getBasketPreview($orderDto);

        return ApiResponse::success($this->serializer->normalize($basketPreview));
    }

    #[Route('/api/v1/order/preview', name: 'app_order_preview', methods: ['POST'])]
    #[OA\Post(
        summary: 'Get order preview',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order preview retrieved successfully',
        content: new OA\JsonContent(
            ref: new Model(type: ResponseShipmentsPreviewAOModel::class)
        )
    )]
    public function getOrderPreview(Request $request): Response
    {
        $orderDto = $this->orderRequestMapper->mapOrderRequest($request);
        $shipmentPreview = $this->shipmentService->getShipmentPreview($orderDto);

        return ApiResponse::success($this->serializer->normalize($shipmentPreview));
    }

    #[Route('/api/v1/order/purchase', name: 'app_order_purchase', methods: ['POST'])]
    #[OA\Post(
        summary: 'Purchase order',
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Order purchased successfully',
        content: new OA\JsonContent(
            ref: new Model(type: ResponseShipmentsPreviewAOModel::class)
        )
    )]
    public function purchaseOrder(Request $request): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $orderDto = $this->orderRequestMapper->mapOrderRequest($request);
        $this->shipmentService->purchaseOrder($orderDto, $user);

        return ApiResponse::success();
    }
}
