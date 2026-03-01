<?php

namespace App\Manager\Shipment;

use App\Dto\Order\OrderItemDto;
use App\Dto\Response\ResponseOrderItemDto;
use App\Dto\Response\ResponseShipmentItemDto;
use App\Dto\Response\ResponseShipmentsPreviewDto;
use App\Dto\Types\ImageDto;
use App\Dto\Types\PriceDto;
use App\Manager\Product\ImageManager;
use App\Manager\Product\ProductManager;
use App\Mapper\Product\ImageMapper;
use App\Util\ShipmentUtil;
use Psr\Log\LoggerInterface;

class ShipmentManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProductManager $productManager,
        private readonly ImageManager $imageManager,
        private readonly ImageMapper $imageMapper,
        private readonly ShipmentUtil $shipmentUtil,
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
}
