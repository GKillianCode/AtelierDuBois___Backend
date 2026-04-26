<?php

namespace App\Tests\Controller\Shipment;

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

class ShipmentControllerTest extends WebTestCase
{
    private static ?User $testUser = null;
    private static ?string $jwtToken = null;

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
            ->setLastname('Shipment')
            ->setEmail('test.shipment.ctrl@example.com');

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

    /**
     * @return ShipmentService&MockObject
     */
    private function mockShipmentService(): MockObject
    {
        $mock = $this->createMock(ShipmentService::class);
        $mock->method('getShipmentHistory')->willReturn(['shipments' => [], 'pagination' => []]);
        $mock->method('getShipmentHistoryYears')->willReturn([2026, 2025]);
        return $mock;
    }

    private function createAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $this->setUpAuthenticatedUser();
        return $client;
    }

    // =========================================================================
    // GET /api/v1/shipment/history
    // =========================================================================

    public function testHistoryRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/shipment/history');

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testHistoryOnlyAcceptsGet(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/shipment/history');

        $this->assertResponseStatusCodeSame(405);
    }

    public function testHistoryWithAuthReturns200(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $client->request('GET', '/api/v1/shipment/history', [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testHistoryResponseHasSuccessTrue(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $client->request('GET', '/api/v1/shipment/history', [], [], $this->authHeaders());

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    public function testHistoryResponseHasDataKey(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $client->request('GET', '/api/v1/shipment/history', [], [], $this->authHeaders());

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $body);
    }

    // =========================================================================
    // GET /api/v1/shipment/history/years
    // =========================================================================

    public function testHistoryYearsRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/shipment/history/years');

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testHistoryYearsOnlyAcceptsGet(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/shipment/history/years');

        $this->assertResponseStatusCodeSame(405);
    }

    public function testHistoryYearsWithAuthReturns200(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $client->request('GET', '/api/v1/shipment/history/years', [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testHistoryYearsResponseHasSuccessTrue(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $client->request('GET', '/api/v1/shipment/history/years', [], [], $this->authHeaders());

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    public function testHistoryYearsResponseHasDataKey(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $client->request('GET', '/api/v1/shipment/history/years', [], [], $this->authHeaders());

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $body);
    }

    public function testHistoryYearsResponseDataIsArray(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $client->request('GET', '/api/v1/shipment/history/years', [], [], $this->authHeaders());

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($body['data']);
    }

    // =========================================================================
    // FORMAT DE RÉPONSE 401
    // =========================================================================

    public function test401ResponseHasExpectedStructure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/shipment/history');

        $this->assertResponseHeaderSame('content-type', 'application/json');
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('message', $body);
    }
}
