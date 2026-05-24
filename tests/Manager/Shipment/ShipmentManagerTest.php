<?php

namespace App\Tests\Manager\Shipment;

use App\Carrier\InternCarrier;
use App\Dto\Order\OrderItemDto;
use App\Dto\Request\Filter\GetShipmentHistoryRequestDto;
use App\Entity\Order\Order;
use App\Entity\Order\OrderProduct;
use App\Entity\Product\Image;
use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Entity\Shipment\Carrier;
use App\Entity\Shipment\OrderStatus;
use App\Entity\Shipment\Shipment;
use App\Entity\Shipment\ShipmentItem;
use App\Entity\User\Address;
use App\Entity\User\User;
use App\Enum\ShipmentStatusCode;
use App\Enum\SortFilter\ShipmentHistorySortFilterCode;
use App\Exception\NotFoundException;
use App\Factory\CarrierFactory;
use App\Manager\Product\ImageManager;
use App\Manager\Product\ProductManager;
use App\Manager\Product\ProductReviewManager;
use App\Manager\Shipment\ShipmentManager;
use App\Manager\Shipment\ShipmentStatusManager;
use App\Manager\User\AddressManager;
use App\Mapper\Product\ImageMapper;
use App\Mapper\Product\ProductVariantMapper;
use App\Repository\Order\OrderRepository;
use App\Repository\Product\ImageRepository;
use App\Repository\Product\ProductReviewRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Repository\Product\ProductVariantRepository;
use App\Repository\Shipment\CarrierRepository;
use App\Service\Product\ReviewRightsService;
use App\Repository\Shipment\OrderStatusRepository;
use App\Repository\Shipment\ShipmentRepository;
use App\Repository\Order\OrderProductRepository;
use App\Repository\User\AddressRepository;
use App\Util\ShipmentUtil;
use App\Util\UuidUtil;
use App\Util\ValidatorUtil;
use App\Util\PaginationUtil;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;

class ShipmentManagerTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private EntityManagerInterface&MockObject $entityManager;
    private ProductVariantRepository&MockObject $productVariantRepository;
    private ImageRepository&MockObject $imageRepository;
    private AddressRepository&MockObject $addressRepository;
    private OrderStatusRepository&MockObject $orderStatusRepository;
    private CarrierRepository&MockObject $carrierRepository;
    private OrderRepository&MockObject $orderRepository;
    private ShipmentRepository&MockObject $shipmentRepository;
    private ShipmentManager $sut;

    protected function setUp(): void
    {
        $this->logger                   = $this->createMock(LoggerInterface::class);
        $this->entityManager            = $this->createMock(EntityManagerInterface::class);
        $this->productVariantRepository = $this->createMock(ProductVariantRepository::class);
        $this->imageRepository          = $this->createMock(ImageRepository::class);
        $this->addressRepository        = $this->createMock(AddressRepository::class);
        $this->orderStatusRepository    = $this->createMock(OrderStatusRepository::class);
        $this->carrierRepository        = $this->createMock(CarrierRepository::class);
        $this->orderRepository    = $this->createMock(OrderRepository::class);
        $this->shipmentRepository = $this->createMock(ShipmentRepository::class);

        $mockLogger    = $this->createMock(LoggerInterface::class);
        $uuidUtil      = new UuidUtil($mockLogger);
        $shipmentUtil  = new ShipmentUtil($uuidUtil);
        $validatorUtil = new ValidatorUtil(
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
            $mockLogger,
        );

        // Pure mappers / unused dependencies — real or minimal mocks
        $imageMapper          = new ImageMapper($mockLogger);
        $productVariantMapper = $this->createMock(ProductVariantMapper::class);
        $productReviewManager = $this->createMock(ProductReviewManager::class);

        // Real managers backed by mocked repositories — no DB required
        $productManager = new ProductManager(
            $mockLogger,
            $this->entityManager,
            $validatorUtil,
            $this->productVariantRepository,
            $productVariantMapper,
            $productReviewManager,
        );

        $imageManager = new ImageManager($this->imageRepository);

        $addressManager = new AddressManager(
            $this->addressRepository,
            $this->entityManager,
            $mockLogger,
            $validatorUtil,
        );

        $shipmentStatusManager = new ShipmentStatusManager($this->orderStatusRepository);

        // Real carrier backed by the mocked repository
        $carrierFactory = new CarrierFactory([new InternCarrier($this->carrierRepository, $uuidUtil)]);

        $this->sut = new ShipmentManager(
            $this->logger,
            $this->entityManager,
            $productManager,
            $imageManager,
            $addressManager,
            $imageMapper,
            $shipmentStatusManager,
            $shipmentUtil,
            $validatorUtil,
            $this->shipmentRepository,
            $this->orderRepository,
            $carrierFactory,
            new PaginationUtil($mockLogger),
            $this->createMock(ReviewRightsService::class),
            $this->createMock(ProductReviewRepository::class),
            $this->productVariantRepository,
            $productReviewManager,
            $this->createMock(OrderProductRepository::class),
        );
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function makeVariant(
        string $publicId = 'PUB1',
        int $price = 1000,
        int $maxStackSize = 5,
        string $productName = 'Table',
        int $id = 1,
    ): ProductVariant {
        $product = (new Product())->setName($productName)->setMaxStackSize($maxStackSize);
        $variant = (new ProductVariant())->setPublicId($publicId)->setPrice($price)->setProductId($product);

        $prop = new \ReflectionProperty(ProductVariant::class, 'id');
        $prop->setValue($variant, $id);

        return $variant;
    }

    private function makeImage(): Image
    {
        return (new Image())->setFolderName('wood')->setImageName('img')->setFormat('jpg');
    }

    /**
     * Builds a minimal Order graph suitable for buildShipmentHistory tests:
     * one Shipment containing one ShipmentItem pointing at the given product.
     */
    private function makeHistoryOrder(
        string $productName,
        string $publicId = 'PUB1',
        int $variantId = 1,
        int $totalPrice = 5000,
        int $quantity = 2,
        string $shipmentPublicId = 'SHP-2604-T6KLBS6G',
    ): Order {
        $product  = (new Product())->setName($productName)->setMaxStackSize(5);
        $variant  = (new ProductVariant())->setPublicId($publicId)->setPrice(1000)->setProductId($product);
        (new \ReflectionProperty(ProductVariant::class, 'id'))->setValue($variant, $variantId);

        $orderProduct = (new OrderProduct())
            ->setProductVariantId($variant)
            ->setQuantity($quantity)
            ->setPrice(1000);

        $shipmentItem = (new ShipmentItem())
            ->setQuantity($quantity)
            ->setOrderProductId($orderProduct);

        $shipment = (new Shipment())->setPublicId($shipmentPublicId);
        $shipment->addShipmentItem($shipmentItem);

        $order = (new Order())->setTotalPrice($totalPrice);
        $order->addShipment($shipment);

        return $order;
    }

    /**
     * Wraps an array of Orders in a Paginator mock so willReturn() satisfies the declared return type.
     *
     * @param Order[] $items
     * @return Paginator<Order>
     */
    private function makePaginator(array $items): Paginator
    {
        $paginator = $this->createMock(Paginator::class);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator($items));

        return $paginator;
    }

    private function makeHistoryRequestDto(
        string $search = '',
        ShipmentHistorySortFilterCode $filter = ShipmentHistorySortFilterCode::ORDERED_ASC,
        ShipmentHistorySortFilterCode $filterName = ShipmentHistorySortFilterCode::ORDERED_ASC,
        ?int $year = null,
        int $page = 1,
        int $limit = 10,
    ): GetShipmentHistoryRequestDto {
        return new GetShipmentHistoryRequestDto($page, $limit, $search, $filter, $filterName, $year);
    }

    // =========================================================================
    // buildBasketItems
    // =========================================================================

    public function testBuildBasketItemsReturnsResponseDtoForKnownVariant(): void
    {
        $variant = $this->makeVariant('PUB1', 2500, 5, 'Chaise');
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn($variant);

        $result = $this->sut->buildBasketItems([new OrderItemDto('PUB1', 3)]);

        $this->assertCount(1, $result);
        $this->assertSame('PUB1', $result[0]->getPublicId());
        $this->assertSame(3, $result[0]->getQuantity());
        $this->assertSame(2500, $result[0]->getPriceInCents());
        $this->assertSame('Chaise', $result[0]->getName());
    }

    public function testBuildBasketItemsSkipsAndLogsWarningForUnknownVariant(): void
    {
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn(null);
        $this->logger->expects($this->once())->method('warning');

        $result = $this->sut->buildBasketItems([new OrderItemDto('UNKNOWN', 1)]);

        $this->assertSame([], $result);
    }

    public function testBuildBasketItemsReturnsEmptyArrayForNoItems(): void
    {
        $result = $this->sut->buildBasketItems([]);

        $this->assertSame([], $result);
    }

    // =========================================================================
    // buildShipmentPreview
    // =========================================================================

    public function testBuildShipmentPreviewComputesCorrectTotalPrice(): void
    {
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn($this->makeVariant('PUB1', 1000, 10));
        $this->imageRepository->method('findOneBy')->willReturn($this->makeImage());

        $result = $this->sut->buildShipmentPreview([new OrderItemDto('PUB1', 3)]);

        $this->assertSame(3000, $result->getTotalPriceInCents()->getAmount());
    }

    public function testBuildShipmentPreviewSplitsAcrossBoxes(): void
    {
        // maxStackSize = 3, quantity = 7 → 3 boxes (3 + 3 + 1)
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn($this->makeVariant('PUB1', 500, 3));
        $this->imageRepository->method('findOneBy')->willReturn($this->makeImage());

        $result = $this->sut->buildShipmentPreview([new OrderItemDto('PUB1', 7)]);

        $this->assertCount(3, $result->getShipments());
        $this->assertSame(3, $result->getShipments()[0]->getQuantity());
        $this->assertSame(3, $result->getShipments()[1]->getQuantity());
        $this->assertSame(1, $result->getShipments()[2]->getQuantity());
    }

    public function testBuildShipmentPreviewSkipsUnknownVariant(): void
    {
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn(null);
        $this->logger->expects($this->once())->method('warning');

        $result = $this->sut->buildShipmentPreview([new OrderItemDto('UNKNOWN', 2)]);

        $this->assertCount(0, $result->getShipments());
    }

    public function testBuildShipmentPreviewThrowsNotFoundExceptionWhenImageMissing(): void
    {
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn($this->makeVariant());
        $this->imageRepository->method('findOneBy')->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->sut->buildShipmentPreview([new OrderItemDto('PUB1', 1)]);
    }

    // =========================================================================
    // buildShipmentPurchase
    // =========================================================================

    public function testBuildShipmentPurchaseThrowsNotFoundExceptionWhenNoDefaultAddress(): void
    {
        $this->addressRepository->method('findOneBy')->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->sut->buildShipmentPurchase([new OrderItemDto('PUB1', 1)], new User());
    }

    public function testBuildShipmentPurchasePersistsEntitiesInOrder(): void
    {
        // quantity=2, maxStackSize=5 → 1 shipment → Order + OrderProduct + Shipment + ShipmentItem = 4 persists
        $this->addressRepository->method('findOneBy')->willReturn(new Address());
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn($this->makeVariant('PUB1', 1000, 5));
        $this->orderStatusRepository->method('findOneBy')->willReturn(new OrderStatus());
        $this->carrierRepository->method('findOneBy')->willReturn(new Carrier());

        $this->entityManager->expects($this->exactly(4))->method('persist');
        $this->entityManager->expects($this->exactly(4))->method('flush');

        $this->sut->buildShipmentPurchase([new OrderItemDto('PUB1', 2)], new User());
    }

    public function testBuildShipmentPurchaseCreatesOneShipmentPerBox(): void
    {
        // quantity=5, maxStackSize=3 → 2 boxes → Order + OrderProduct + 2×(Shipment + ShipmentItem) = 6 persists
        $this->addressRepository->method('findOneBy')->willReturn(new Address());
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn($this->makeVariant('PUB1', 500, 3));
        $this->orderStatusRepository->method('findOneBy')->willReturn(new OrderStatus());
        $this->carrierRepository->method('findOneBy')->willReturn(new Carrier());

        $this->entityManager->expects($this->exactly(6))->method('persist');

        $this->sut->buildShipmentPurchase([new OrderItemDto('PUB1', 5)], new User());
    }

    public function testBuildShipmentPurchaseSkipsUnknownVariantAndStillPersistsOrder(): void
    {
        $this->addressRepository->method('findOneBy')->willReturn(new Address());
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn(null);
        $this->logger->expects($this->once())->method('warning');

        // Only Order is persisted — no OrderProduct/Shipment/ShipmentItem
        $this->entityManager->expects($this->once())->method('persist');

        $this->sut->buildShipmentPurchase([new OrderItemDto('UNKNOWN', 2)], new User());
    }

    public function testBuildShipmentPurchaseThrowsNotFoundExceptionWhenShipmentStatusMissing(): void
    {
        // Address and variant found, but no matching OrderStatus → NotFoundException propagated
        $this->addressRepository->method('findOneBy')->willReturn(new Address());
        $this->productVariantRepository->method('getProductVariantByPublicId')->willReturn($this->makeVariant());
        $this->orderStatusRepository->method('findOneBy')->willReturn(null);

        // Inner catch logs "Shipment status code not found", then outer catch logs the generic error
        $this->logger->expects($this->atLeastOnce())->method('error');
        $this->expectException(NotFoundException::class);

        $this->sut->buildShipmentPurchase([new OrderItemDto('PUB1', 1)], new User());
    }

    public function testBuildShipmentPurchaseLogsErrorAndRethrowsOnException(): void
    {
        $this->addressRepository->method('findOneBy')
            ->willThrowException(new \RuntimeException('DB down'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Error building shipment purchase', $this->arrayHasKey('exception'));

        $this->expectException(\RuntimeException::class);

        $this->sut->buildShipmentPurchase([new OrderItemDto('PUB1', 1)], new User());
    }

    // =========================================================================
    // buildShipmentHistory
    // =========================================================================

    public function testBuildShipmentHistoryReturnsEmptyArrayWhenNoOrders(): void
    {
        $this->orderRepository->method('paginateHistoryOrders')->willReturn($this->makePaginator([]));

        $result = $this->sut->buildShipmentHistory(new User(), $this->makeHistoryRequestDto());

        $this->assertSame([], $result['shipments']);
    }

    public function testBuildShipmentHistoryReturnsOneResultPerOrder(): void
    {
        $order = $this->makeHistoryOrder('Chaise', 'PUB1', 1, 3000, 3);
        $this->orderRepository->method('paginateHistoryOrders')->willReturn($this->makePaginator([$order]));
        $this->imageRepository->method('findOneBy')->willReturn($this->makeImage());

        $result = $this->sut->buildShipmentHistory(new User(), $this->makeHistoryRequestDto());

        $this->assertCount(1, $result['shipments']);
    }

    public function testBuildShipmentHistoryMapsTotalPrice(): void
    {
        $order = $this->makeHistoryOrder('Chaise', 'PUB1', 1, 3000, 3);
        $this->orderRepository->method('paginateHistoryOrders')->willReturn($this->makePaginator([$order]));
        $this->imageRepository->method('findOneBy')->willReturn($this->makeImage());

        $result = $this->sut->buildShipmentHistory(new User(), $this->makeHistoryRequestDto());

        $this->assertSame(3000, $result['shipments'][0]->getTotalPriceInCents()->getAmount());
    }

    public function testBuildShipmentHistoryMapsShipmentId(): void
    {
        $order = $this->makeHistoryOrder('Chaise', 'PUB1', 1, 3000, 3, 'SHP-2604-T6KLBS6G');
        $this->orderRepository->method('paginateHistoryOrders')->willReturn($this->makePaginator([$order]));
        $this->imageRepository->method('findOneBy')->willReturn($this->makeImage());

        $result = $this->sut->buildShipmentHistory(new User(), $this->makeHistoryRequestDto());

        $this->assertSame('SHP-2604-T6KLBS6G', $result['shipments'][0]->getShipmentId());
    }

    public function testBuildShipmentHistoryMapsItemData(): void
    {
        $order = $this->makeHistoryOrder('Chaise', 'PUB1', 1, 3000, 3);
        $this->orderRepository->method('paginateHistoryOrders')->willReturn($this->makePaginator([$order]));
        $this->imageRepository->method('findOneBy')->willReturn($this->makeImage());

        $result = $this->sut->buildShipmentHistory(new User(), $this->makeHistoryRequestDto());

        $item = $result['shipments'][0]->getShipments()[0];
        $this->assertSame('Chaise', $item->getName());
        $this->assertSame('PUB1', $item->getPublicId());
        $this->assertSame(3, $item->getQuantity());
    }

    public function testBuildShipmentHistorySkipsOrdersWhereAllItemsFilteredBySearch(): void
    {
        $order = $this->makeHistoryOrder('Chaise');
        $this->orderRepository->method('paginateHistoryOrders')->willReturn($this->makePaginator([$order]));
        // Search eliminates every item before the image fetch: imageRepository must not be called
        $this->imageRepository->expects($this->never())->method('findOneBy');

        $result = $this->sut->buildShipmentHistory(new User(), $this->makeHistoryRequestDto(search: 'Table'));

        $this->assertSame([], $result['shipments']);
    }

    public function testBuildShipmentHistoryFiltersItemsByPartialSearchAndKeepsMatchingOnes(): void
    {
        // Two items in the same shipment: 'Chaise' matches 'chai', 'Table' does not
        $product1 = (new Product())->setName('Chaise')->setMaxStackSize(5);
        $variant1 = (new ProductVariant())->setPublicId('PUB1')->setPrice(1000)->setProductId($product1);
        (new \ReflectionProperty(ProductVariant::class, 'id'))->setValue($variant1, 1);

        $product2 = (new Product())->setName('Table')->setMaxStackSize(5);
        $variant2 = (new ProductVariant())->setPublicId('PUB2')->setPrice(1000)->setProductId($product2);
        (new \ReflectionProperty(ProductVariant::class, 'id'))->setValue($variant2, 2);

        $item1 = (new ShipmentItem())->setQuantity(1)
            ->setOrderProductId((new OrderProduct())->setProductVariantId($variant1)->setQuantity(1)->setPrice(1000));
        $item2 = (new ShipmentItem())->setQuantity(1)
            ->setOrderProductId((new OrderProduct())->setProductVariantId($variant2)->setQuantity(1)->setPrice(1000));

        $shipment = new Shipment();
        $shipment->addShipmentItem($item1);
        $shipment->addShipmentItem($item2);

        $order = (new Order())->setTotalPrice(2000);
        $order->addShipment($shipment);

        $this->orderRepository->method('paginateHistoryOrders')->willReturn($this->makePaginator([$order]));
        $this->imageRepository->method('findOneBy')->willReturn($this->makeImage());

        $result = $this->sut->buildShipmentHistory(new User(), $this->makeHistoryRequestDto(search: 'chai'));

        $this->assertCount(1, $result['shipments']);
        $names = array_map(fn($s) => $s->getName(), $result['shipments'][0]->getShipments());
        $this->assertSame(['Chaise'], $names);
    }

    public function testBuildShipmentHistorySortsItemsByNameAscWithinOrder(): void
    {
        $product1 = (new Product())->setName('Zèbre')->setMaxStackSize(5);
        $variant1 = (new ProductVariant())->setPublicId('PUB1')->setPrice(1000)->setProductId($product1);
        (new \ReflectionProperty(ProductVariant::class, 'id'))->setValue($variant1, 1);

        $product2 = (new Product())->setName('Armoire')->setMaxStackSize(5);
        $variant2 = (new ProductVariant())->setPublicId('PUB2')->setPrice(1000)->setProductId($product2);
        (new \ReflectionProperty(ProductVariant::class, 'id'))->setValue($variant2, 2);

        $item1 = (new ShipmentItem())->setQuantity(1)
            ->setOrderProductId((new OrderProduct())->setProductVariantId($variant1)->setQuantity(1)->setPrice(1000));
        $item2 = (new ShipmentItem())->setQuantity(1)
            ->setOrderProductId((new OrderProduct())->setProductVariantId($variant2)->setQuantity(1)->setPrice(1000));

        $shipment = new Shipment();
        $shipment->addShipmentItem($item1);
        $shipment->addShipmentItem($item2);

        $order = (new Order())->setTotalPrice(2000);
        $order->addShipment($shipment);

        $this->orderRepository->method('paginateHistoryOrders')->willReturn($this->makePaginator([$order]));
        $this->imageRepository->method('findOneBy')->willReturn($this->makeImage());

        $result = $this->sut->buildShipmentHistory(
            new User(),
            $this->makeHistoryRequestDto(filterName: ShipmentHistorySortFilterCode::NAME_ASC),
        );

        $names = array_map(fn($s) => $s->getName(), $result['shipments'][0]->getShipments());
        // After transliteration: 'zebre' > 'armoire' → NAME_ASC puts 'Armoire' first
        $this->assertSame(['Armoire', 'Zèbre'], $names);
    }

    public function testBuildShipmentHistorySortsItemsByNameDescWithinOrder(): void
    {
        $product1 = (new Product())->setName('Armoire')->setMaxStackSize(5);
        $variant1 = (new ProductVariant())->setPublicId('PUB1')->setPrice(1000)->setProductId($product1);
        (new \ReflectionProperty(ProductVariant::class, 'id'))->setValue($variant1, 1);

        $product2 = (new Product())->setName('Zèbre')->setMaxStackSize(5);
        $variant2 = (new ProductVariant())->setPublicId('PUB2')->setPrice(1000)->setProductId($product2);
        (new \ReflectionProperty(ProductVariant::class, 'id'))->setValue($variant2, 2);

        $item1 = (new ShipmentItem())->setQuantity(1)
            ->setOrderProductId((new OrderProduct())->setProductVariantId($variant1)->setQuantity(1)->setPrice(1000));
        $item2 = (new ShipmentItem())->setQuantity(1)
            ->setOrderProductId((new OrderProduct())->setProductVariantId($variant2)->setQuantity(1)->setPrice(1000));

        $shipment = new Shipment();
        $shipment->addShipmentItem($item1);
        $shipment->addShipmentItem($item2);

        $order = (new Order())->setTotalPrice(2000);
        $order->addShipment($shipment);

        $this->orderRepository->method('paginateHistoryOrders')->willReturn($this->makePaginator([$order]));
        $this->imageRepository->method('findOneBy')->willReturn($this->makeImage());

        $result = $this->sut->buildShipmentHistory(
            new User(),
            $this->makeHistoryRequestDto(filterName: ShipmentHistorySortFilterCode::NAME_DESC),
        );

        $names = array_map(fn($s) => $s->getName(), $result['shipments'][0]->getShipments());
        $this->assertSame(['Zèbre', 'Armoire'], $names);
    }

    // =========================================================================
    // buildShipmentDetail
    // =========================================================================

    private function makeDetailShipment(
        string $shipmentPublicId = 'SHP-2604-T6KLBS6G',
        ShipmentStatusCode $statusCode = ShipmentStatusCode::PENDING,
        string $statusName = 'En attente',
    ): Shipment {
        $status   = (new OrderStatus())->setCode($statusCode)->setName($statusName);
        $order    = new Order();
        $shipment = (new Shipment())->setPublicId($shipmentPublicId)->setStatusId($status)->setOrderId($order);
        $order->addShipment($shipment);

        return $shipment;
    }

    public function testBuildShipmentDetailThrowsNotFoundWhenShipmentNotFound(): void
    {
        $this->shipmentRepository->method('findByPublicIdForUser')->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->sut->buildShipmentDetail('SHP-2604-NOTFOUND', new User());
    }

    public function testBuildShipmentDetailReturnsDtoWithCorrectShipmentIdAndStatus(): void
    {
        $shipment = $this->makeDetailShipment('SHP-2604-T6KLBS6G', ShipmentStatusCode::IN_TRANSIT, 'En transit');
        $this->shipmentRepository->method('findByPublicIdForUser')->willReturn($shipment);

        $result = $this->sut->buildShipmentDetail('SHP-2604-T6KLBS6G', new User());

        $this->assertSame('SHP-2604-T6KLBS6G', $result->getShipmentId());
        $this->assertSame('IN_TRANSIT', $result->getStatus()->getCode());
        $this->assertSame('En transit', $result->getStatus()->getName());
        $this->assertSame([], $result->getItems());
    }

    public function testBuildShipmentDetailAggregatesItemsFromAllShipmentsOfOrder(): void
    {
        $product1 = (new Product())->setName('Chaise')->setMaxStackSize(5);
        $variant1 = (new ProductVariant())->setPublicId('PUB1')->setPrice(1000)->setProductId($product1);
        (new \ReflectionProperty(ProductVariant::class, 'id'))->setValue($variant1, 1);

        $product2 = (new Product())->setName('Table')->setMaxStackSize(5);
        $variant2 = (new ProductVariant())->setPublicId('PUB2')->setPrice(1000)->setProductId($product2);
        (new \ReflectionProperty(ProductVariant::class, 'id'))->setValue($variant2, 2);

        $item1 = (new ShipmentItem())->setQuantity(1)
            ->setOrderProductId((new OrderProduct())->setProductVariantId($variant1)->setQuantity(1)->setPrice(1000));
        $item2 = (new ShipmentItem())->setQuantity(1)
            ->setOrderProductId((new OrderProduct())->setProductVariantId($variant2)->setQuantity(1)->setPrice(1000));

        $status    = (new OrderStatus())->setCode(ShipmentStatusCode::PENDING)->setName('En attente');
        $order     = new Order();
        $shipment1 = (new Shipment())->setPublicId('SHP-2604-T6KLBS6G')->setStatusId($status)->setOrderId($order);
        $shipment1->addShipmentItem($item1);
        $shipment2 = (new Shipment())->setPublicId('SHP-2604-XXXXXXXX')->setStatusId($status)->setOrderId($order);
        $shipment2->addShipmentItem($item2);
        $order->addShipment($shipment1);
        $order->addShipment($shipment2);

        $this->shipmentRepository->method('findByPublicIdForUser')->willReturn($shipment1);
        $this->imageRepository->method('findOneBy')->willReturn($this->makeImage());

        $result = $this->sut->buildShipmentDetail('SHP-2604-T6KLBS6G', new User());

        $this->assertCount(2, $result->getItems());
        $names = array_map(fn($i) => $i->getName(), $result->getItems());
        $this->assertSame(['Chaise', 'Table'], $names);
    }
}
