<?php

namespace App\Manager\Shipment;

use App\Carrier\CarrierBase;
use App\Dto\Order\OrderItemDto;
use App\Dto\Request\Filter\GetShipmentHistoryRequestDto;
use App\Dto\Response\ResponseOrderItemDto;
use App\Dto\Response\ResponseShipmentItemDto;
use App\Dto\Response\ResponseShipmentsHistoryDto;
use App\Dto\Response\ResponseShipmentsPreviewDto;
use App\Dto\Types\ImageDto;
use App\Dto\Types\PriceDto;
use App\Entity\Order\Order;
use App\Entity\Order\OrderProduct;
use App\Entity\Shipment\Shipment;
use App\Entity\Shipment\ShipmentItem;
use App\Entity\User\User;
use App\Enum\ShipmentStatusCode;
use App\Enum\SortFilter\ShipmentHistorySortFilterCode;
use App\Exception\NotFoundException;
use App\Manager\Product\ImageManager;
use App\Manager\Product\ProductManager;
use App\Manager\User\AddressManager;
use App\Mapper\Product\ImageMapper;
use App\Repository\Order\OrderRepository;
use App\Repository\Shipment\ShipmentRepository;
use App\Trait\ValidateAndSaveTrait;
use App\Util\ShipmentUtil;
use App\Util\ValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ShipmentManager
{
    use ValidateAndSaveTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ProductManager $productManager,
        private readonly ImageManager $imageManager,
        private readonly AddressManager $addressManager,
        private readonly ImageMapper $imageMapper,
        private readonly ShipmentStatusManager $shipmentStatusManager,
        private readonly ShipmentUtil $shipmentUtil,
        private readonly ValidatorUtil $validatorUtil,
        private readonly CarrierBase $carrierBase,
        private readonly ShipmentRepository $shipmentRepository,
        private readonly OrderRepository $orderRepository,
    ) {}

    /**
     * @param OrderItemDto[] $orderItems
     * @return ResponseOrderItemDto[]
     */
    public function buildBasketItems(array $orderItems): array
    {
        $basketItems = [];

        foreach ($orderItems as $item) {
            $productVariant = $this->productManager->getProductVariantByPublicId($item->getPublicId());

            if (!$productVariant) {
                $this->logger->warning('Product variant not found for basket item', ['publicId' => $item->getPublicId()]);
                continue;
            }

            $basketItems[] = new ResponseOrderItemDto(
                publicId: $item->getPublicId(),
                quantity: $item->getQuantity(),
                name: $productVariant->getProductId()->getName(),
                priceInCents: $productVariant->getPrice(),
            );
        }

        return $basketItems;
    }

    /**
     * @param OrderItemDto[] $orderItems
     */
    public function buildShipmentPreview(array $orderItems): ResponseShipmentsPreviewDto
    {
        $shipmentItems = [];
        $totalPriceInCents = 0;

        foreach ($orderItems as $item) {
            $productVariant = $this->productManager->getProductVariantByPublicId($item->getPublicId());

            if (!$productVariant) {
                $this->logger->warning('Product variant not found for shipment preview', ['publicId' => $item->getPublicId()]);
                continue;
            }

            $maxStackSize = $productVariant->getProductId()->getMaxStackSize();
            $numberOfShipments = $this->shipmentUtil->calculateNumberOfShipments($item->getQuantity(), $maxStackSize);
            $totalPriceInCents += $item->getQuantity() * $productVariant->getPrice();

            // Fetch the image once per variant, not once per shipment box
            $imageDto = $this->imageMapper->toDtoFromEntity(
                $this->imageManager->getDefaultImageForVariant($productVariant->getId())
            );

            for ($i = 0; $i < $numberOfShipments; $i++) {
                $quantityInBox = min($item->getQuantity() - ($i * $maxStackSize), $maxStackSize);

                $shipmentItems[] = new ResponseShipmentItemDto(
                    publicId: $item->getPublicId(),
                    name: $productVariant->getProductId()->getName(),
                    quantity: $quantityInBox,
                    mainImage: new ImageDto($imageDto->getImageUrl()),
                );
            }
        }

        return new ResponseShipmentsPreviewDto(
            shipments: $shipmentItems,
            totalPriceInCents: new PriceDto($totalPriceInCents),
        );
    }

    /**
     * @param OrderItemDto[] $orderItems
     */
    public function buildShipmentPurchase(array $orderItems, User $user): void
    {
        try {
            $defaultUserAddress = $this->addressManager->getDefaultAddressForUser($user);
            if (!$defaultUserAddress) {
                throw new NotFoundException("address", "default");
            }

            $totalPriceInCents = 0;
            $resolvedItems = [];
            foreach ($orderItems as $item) {
                $productVariant = $this->productManager->getProductVariantByPublicId($item->getPublicId());
                if (!$productVariant) {
                    $this->logger->warning('Product variant not found for shipment purchase', ['publicId' => $item->getPublicId()]);
                    continue;
                }
                $totalPriceInCents += $item->getQuantity() * $productVariant->getPrice();
                $resolvedItems[] = ['item' => $item, 'variant' => $productVariant];
            }

            $order = new Order();
            $order->setOrderNumber($this->shipmentUtil->generateNewOrderNumber())
                ->setUserId($user)
                ->setTotalPrice($totalPriceInCents);

            $this->validateAndSave($order);

            if (empty($resolvedItems)) {
                return;
            }

            $shipmentStatusCode = $this->shipmentStatusManager->getShipmentStatusByCode(ShipmentStatusCode::PENDING->value);

            foreach ($resolvedItems as ['item' => $item, 'variant' => $productVariant]) {
                $maxStackSize = $productVariant->getProductId()->getMaxStackSize();
                $numberOfShipments = $this->shipmentUtil->calculateNumberOfShipments($item->getQuantity(), $maxStackSize);

                $orderProduct = new OrderProduct();
                $orderProduct->setOrderId($order)
                    ->setProductVariantId($productVariant)
                    ->setQuantity($item->getQuantity())
                    ->setPrice($productVariant->getPrice());

                $this->validateAndSave($orderProduct);

                for ($i = 0; $i < $numberOfShipments; $i++) {
                    $quantityInBox = min($item->getQuantity() - ($i * $maxStackSize), $maxStackSize);

                    // Each box gets its own tracking number; carrier entity is shared
                    $carrier = $this->carrierBase->createCarrierShipment();
                    $shipment = new Shipment();
                    $shipment->setDeliveryAddressId($defaultUserAddress)
                        ->setBillingAddressId($defaultUserAddress)
                        ->setOrderNumber($order->getOrderNumber())
                        ->setTrackingNumber($carrier->getTrackingNumber())
                        ->setOrderId($order)
                        ->setStatusId($shipmentStatusCode)
                        ->setCarrierId($carrier->getCarrier());

                    $this->validateAndSave($shipment);

                    $shipmentItem = new ShipmentItem();
                    $shipmentItem->setShipmentId($shipment)
                        ->setQuantity($quantityInBox)
                        ->setOrderProductId($orderProduct);

                    $this->validateAndSave($shipmentItem);
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error building shipment purchase', [
                'exception' => $e->getMessage(),
                'userId' => $user->getId()
            ]);

            throw $e;
        }
    }

    public function buildShipmentHistory(User $user, GetShipmentHistoryRequestDto $getShipmentHistoryRequestDto): array
    {
        $orders = $this->orderRepository->paginateHistoryOrders($getShipmentHistoryRequestDto, $user);

        $shipmentHistory = [];
        $rawSearch = $getShipmentHistoryRequestDto->getSearch() ?? '';
        $normalizedSearch = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $rawSearch);

        foreach ($orders as $order) {
            $shipmentItems = [];
            foreach ($order->getShipments() as $shipment) {
                foreach ($shipment->getShipmentItems() as $shipmentItem) {
                    $name = $shipmentItem->getOrderProductId()->getProductVariantId()->getProductId()->getName();

                    if ($rawSearch !== '' && !str_contains(
                        transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $name),
                        $normalizedSearch
                    )) {
                        continue;
                    }

                    $shipmentItems[] = new ResponseShipmentItemDto(
                        publicId: $shipmentItem->getOrderProductId()->getProductVariantId()->getPublicId(),
                        name: $shipmentItem->getOrderProductId()->getProductVariantId()->getProductId()->getName(),
                        quantity: $shipmentItem->getQuantity(),
                        mainImage: new ImageDto($this->imageMapper->toDtoFromEntity(
                            $this->imageManager->getDefaultImageForVariant($shipmentItem->getOrderProductId()->getProductVariantId()->getId())
                        )->getImageUrl()),
                    );
                }
            }

            if ($getShipmentHistoryRequestDto->getFilterName() === ShipmentHistorySortFilterCode::NAME_ASC || $getShipmentHistoryRequestDto->getFilterName() === ShipmentHistorySortFilterCode::NAME_DESC) {
                usort($shipmentItems, fn($a, $b) => strcmp(
                    $a->getName(),
                    $b->getName()
                ) * ($getShipmentHistoryRequestDto->getFilterName() === ShipmentHistorySortFilterCode::NAME_ASC ? 1 : -1));
            }

            if (empty($shipmentItems)) {
                continue;
            }
            $shipmentHistory[] = new ResponseShipmentsHistoryDto(
                shipments: $shipmentItems,
                totalPriceInCents: new PriceDto($order->getTotalPrice()),
                orderedAt: $order->getCreatedAt()->getTimestamp(),
            );
        }

        return $shipmentHistory;
    }
}
