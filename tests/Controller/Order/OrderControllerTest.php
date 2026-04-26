<?php

namespace App\Tests\Controller\Order;

use App\Entity\User\User;
use App\Enum\UserType;
use App\Service\Shipment\ShipmentService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class OrderControllerTest extends WebTestCase
{
    private static ?User $testUser = null;
    private static ?string $jwtToken = null;

    private const VALID_ITEMS = [
        ['publicId' => 'aB3dEfGhIjKlMnOpQrStuV', 'quantity' => 1],
    ];

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    private function setUpAuthenticatedUser(): void
    {
        if (self::$testUser !== null) {
            return;
        }

        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setUuid(Uuid::v4()->toRfc4122())
            ->setUserType(UserType::CUSTOMER)
            ->setFirstname('Test')
            ->setLastname('Order')
            ->setEmail('test.order.ctrl@example.com');

        $user->setPassword($hasher->hashPassword($user, 'Abricot2024!'));

        $em->persist($user);
        $em->flush();

        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $container->get(JWTTokenManagerInterface::class);

        self::$testUser = $user;
        self::$jwtToken = $jwtManager->create($user);
    }

    protected function tearDown(): void
    {
        if (self::$testUser !== null) {
            $em = static::getContainer()->get(EntityManagerInterface::class);
            $user = $em->find(User::class, self::$testUser->getId());
            if ($user) {
                $em->remove($user);
                $em->flush();
            }
            self::$testUser = null;
            self::$jwtToken = null;
        }

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @return array<string, string> */
    private function authHeaders(): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . self::$jwtToken];
    }

    /** @return array<string, string> */
    private function jsonHeaders(): array
    {
        return ['CONTENT_TYPE' => 'application/json'];
    }

    /** @param array<string, mixed> $data */
    private function postJson(KernelBrowser $client, string $uri, array $data, bool $withAuth = false): void
    {
        $headers = $this->jsonHeaders();
        if ($withAuth) {
            $headers = array_merge($headers, $this->authHeaders());
        }
        $client->request('POST', $uri, [], [], $headers, json_encode($data));
    }

    /**
     * @return ShipmentService&MockObject
     */
    private function mockShipmentService(): MockObject
    {
        $mock = $this->createMock(ShipmentService::class);
        $mock->method('getBasketPreview')->willReturn([]);
        $mock->method('getShipmentPreview')->willReturn($this->createMock(\App\Dto\Response\ResponseShipmentsPreviewDto::class));
        // purchaseOrder is void — no willReturn needed
        return $mock;
    }

    private function createAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $this->setUpAuthenticatedUser();
        return $client;
    }

    // =========================================================================
    // POST /api/v1/order/basket
    // =========================================================================

    public function testBasketRequiresAuth(): void
    {
        $client = static::createClient();
        $this->postJson($client, '/api/v1/order/basket', self::VALID_ITEMS);

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testBasketOnlyAcceptsPost(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/order/basket');

        $this->assertResponseStatusCodeSame(405);
    }

    public function testBasketWithValidPayloadReturns200(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $this->postJson($client, '/api/v1/order/basket', self::VALID_ITEMS, true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testBasketResponseHasSuccessTrue(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $this->postJson($client, '/api/v1/order/basket', self::VALID_ITEMS, true);

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    public function testBasketWithEmptyBodyReturns400(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/v1/order/basket', [], [], array_merge($this->authHeaders(), $this->jsonHeaders()), '[]');

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testBasketWithInvalidJsonReturns400(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/v1/order/basket', [], [], array_merge($this->authHeaders(), $this->jsonHeaders()), 'not-json');

        $this->assertResponseStatusCodeSame(400);
    }

    // =========================================================================
    // POST /api/v1/order/preview
    // =========================================================================

    public function testPreviewRequiresAuth(): void
    {
        $client = static::createClient();
        $this->postJson($client, '/api/v1/order/preview', self::VALID_ITEMS);

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testPreviewOnlyAcceptsPost(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/order/preview');

        $this->assertResponseStatusCodeSame(405);
    }

    public function testPreviewWithValidPayloadReturns200(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $this->postJson($client, '/api/v1/order/preview', self::VALID_ITEMS, true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testPreviewResponseHasSuccessTrue(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $this->postJson($client, '/api/v1/order/preview', self::VALID_ITEMS, true);

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    public function testPreviewWithEmptyBodyReturns400(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/v1/order/preview', [], [], array_merge($this->authHeaders(), $this->jsonHeaders()), '[]');

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    // =========================================================================
    // POST /api/v1/order/purchase
    // =========================================================================

    public function testPurchaseRequiresAuth(): void
    {
        $client = static::createClient();
        $this->postJson($client, '/api/v1/order/purchase', self::VALID_ITEMS);

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testPurchaseOnlyAcceptsPost(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/order/purchase');

        $this->assertResponseStatusCodeSame(405);
    }

    public function testPurchaseWithValidPayloadReturns200(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $this->postJson($client, '/api/v1/order/purchase', self::VALID_ITEMS, true);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testPurchaseResponseHasSuccessTrue(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $this->postJson($client, '/api/v1/order/purchase', self::VALID_ITEMS, true);

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    public function testPurchaseWithEmptyBodyReturns400(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/v1/order/purchase', [], [], array_merge($this->authHeaders(), $this->jsonHeaders()), '[]');

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    // =========================================================================
    // FORMAT DE RÉPONSE 401
    // =========================================================================

    public function test401ResponseHasExpectedStructure(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/order/basket', [], [], $this->jsonHeaders(), '[]');

        $this->assertResponseHeaderSame('content-type', 'application/json');
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('message', $body);
    }
}
