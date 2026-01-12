<?php

namespace App\Controller\Order;

use Psr\Log\LoggerInterface;
use App\Voter\PlaceOrderVoter;
use App\Service\Order\OrderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class OrderController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly OrderService $orderService,
    ) {}

    #[IsGranted(PlaceOrderVoter::CAN_PLACE_ORDER)]
    #[Route('/api/v1/order/all', name: 'app_order_order')]
    public function getAllOrders(Request $request): Response
    {
        try {
            $this->logger->info("OrderController::index ENTER");
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 20);
            $user = $this->getUser();
            $data = $this->orderService->getAllOrders($page, $limit, $user);

            $this->logger->info("OrderController::index EXIT");

            return $this->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error("OrderController::index ERROR: " . $e->getMessage());
            return $this->json([
                'status' => 'Error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
