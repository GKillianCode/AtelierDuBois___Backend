<?php

namespace App\Controller\Shipment;

use App\Voter\CancelShipmentVoter;
use App\Dto\Response\ResponseShipmentDetailDto;
use App\Entity\User\User;
use App\Mapper\Request\ReviewRequestMapper;
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
        private readonly ReviewRequestMapper $reviewRequestMapper,
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

    #[Route('/api/v1/shipment/{publicId}/review-rights', name: 'shipment_review_rights', methods: ['GET'])]
    #[OA\Get(summary: 'Get review rights for each item of a shipment')]
    #[OA\Parameter(name: 'publicId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Review rights retrieved successfully',
    )]
    public function getReviewRights(string $publicId): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        $validatedPublicId = $this->shipmentDetailRequestMapper->mapPublicId($publicId);
        $rights = $this->shipmentService->getReviewRights($validatedPublicId, $user);

        return ApiResponse::success($this->serializer->normalize($rights));
    }

    #[Route('/api/v1/shipment/{publicId}/review', name: 'shipment_review_add', methods: ['POST'])]
    #[OA\Post(summary: 'Add a review for a product variant in a shipment')]
    #[OA\Parameter(name: 'publicId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: Response::HTTP_CREATED, description: 'Review created successfully')]
    public function addReview(Request $request, string $publicId): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        $validatedPublicId = $this->shipmentDetailRequestMapper->mapPublicId($publicId);
        $dto = $this->reviewRequestMapper->mapAddReviewRequest($request);
        $this->shipmentService->addReview($validatedPublicId, $user, $dto);

        return ApiResponse::created();
    }

    #[Route('/api/v1/shipment/{publicId}/review/{variantPublicId}', name: 'shipment_review_edit', methods: ['PUT'])]
    #[OA\Put(summary: 'Edit a review for a product variant in a shipment')]
    #[OA\Parameter(name: 'publicId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'variantPublicId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: Response::HTTP_OK, description: 'Review updated successfully')]
    public function editReview(Request $request, string $publicId, string $variantPublicId): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        $validatedPublicId = $this->shipmentDetailRequestMapper->mapPublicId($publicId);
        $dto = $this->reviewRequestMapper->mapEditReviewRequest($request);
        $this->shipmentService->editReview($validatedPublicId, $variantPublicId, $user, $dto);

        return ApiResponse::success();
    }

    #[Route('/api/v1/shipment/{publicId}/review/{variantPublicId}', name: 'shipment_review_delete', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Delete a review for a product variant in a shipment')]
    #[OA\Parameter(name: 'publicId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'variantPublicId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: Response::HTTP_OK, description: 'Review deleted successfully')]
    public function deleteReview(string $publicId, string $variantPublicId): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        $validatedPublicId = $this->shipmentDetailRequestMapper->mapPublicId($publicId);
        $this->shipmentService->deleteReview($validatedPublicId, $variantPublicId, $user);

        return ApiResponse::success();
    }

    #[Route('/api/v1/shipment/{publicId}/cancel', name: 'shipment_cancel', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Cancel a shipment')]
    #[OA\Parameter(name: 'publicId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: Response::HTTP_OK, description: 'Shipment cancelled successfully')]
    #[OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Cancellation window has expired')]
    public function cancel(string $publicId): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        $validatedPublicId = $this->shipmentDetailRequestMapper->mapPublicId($publicId);
        $order = $this->shipmentService->getOrderByShipmentPublicId($validatedPublicId, $user);

        $this->denyAccessUnlessGranted(CancelShipmentVoter::CAN_CANCEL_SHIPMENT, $order);

        $this->shipmentService->cancelShipment($validatedPublicId, $user);

        return ApiResponse::success();
    }
}

