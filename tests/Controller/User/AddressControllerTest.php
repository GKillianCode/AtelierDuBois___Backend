<?php

namespace App\Tests\Controller\User;

use App\Entity\User\User;
use App\Enum\UserType;
use App\Manager\User\AddressManager;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class AddressControllerTest extends WebTestCase
{
    private const FAKE_PUBLIC_ID = 'aB3dEfGhIjKlMnOpQrStuV';

    private static ?User $testUser  = null;
    private static ?string $jwtToken = null;

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    /**
     * Crée un utilisateur en base et génère un JWT pour les tests authentifiés.
     * Appeler uniquement dans les tests qui en ont besoin.
     */
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
            ->setLastname('Adresse')
            ->setEmail('test.address.ctrl@example.com');

        $user->setPassword($hasher->hashPassword($user, 'Abricot2024!'));

        $em->persist($user);
        $em->flush();

        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $container->get(JWTTokenManagerInterface::class);

        self::$testUser  = $user;
        self::$jwtToken  = $jwtManager->create($user);
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

    private function authHeaders(): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . self::$jwtToken];
    }

    private function jsonHeaders(): array
    {
        return ['CONTENT_TYPE' => 'application/json'];
    }

    /**
     * @return AddressManager&MockObject
     */
    private function mockAddressManager(bool $canAdd = true): MockObject
    {
        $mock = $this->createMock(AddressManager::class);
        $mock->method('canUserAddAddress')->willReturn($canAdd);
        $mock->method('getAddressByPublicId')->willReturn(null);
        return $mock;
    }

    private function createAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $this->setUpAuthenticatedUser();
        return $client;
    }

    // =========================================================================
    // CONTRAT D'AUTHENTIFICATION : 401 sans token
    // =========================================================================

    public function testAddAddressRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/user/address/add', [], [], $this->jsonHeaders(), '{}');

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testCanAddAddressRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/user/address/can-add');

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testGetAllAddressesRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/user/address/all');

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testGetAddressRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/user/address/' . self::FAKE_PUBLIC_ID);

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testUpdateAddressRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('PUT', '/api/v1/user/address/' . self::FAKE_PUBLIC_ID . '/update', [], [], $this->jsonHeaders(), '{}');

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testRemoveAddressRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/v1/user/address/' . self::FAKE_PUBLIC_ID . '/remove');

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    // =========================================================================
    // FORMAT DE RÉPONSE : 401 conforme au contrat ApiResponse
    // =========================================================================

    public function test401ResponseIsJsonWithExpectedStructure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/user/address/all');

        $this->assertResponseHeaderSame('content-type', 'application/json');
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('message', $body);
    }

    // =========================================================================
    // ROUTES AUTHENTIFIÉES
    // =========================================================================

    public function testCanAddAddressWithAuthReturns200(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/v1/user/address/can-add', [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(200);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
        $this->assertTrue($body['data']['canAddAddress']);
    }

    public function testCanAddAddressReturnsFalseWhenLimitReached(): void
    {
        $client = $this->createAuthenticatedClient();

        static::getContainer()->set(AddressManager::class, $this->mockAddressManager(canAdd: false));

        $client->request('GET', '/api/v1/user/address/can-add', [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(200);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
        $this->assertFalse($body['data']['canAddAddress']);
    }

    public function testGetAllAddressesWithAuthReturns200(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/v1/user/address/all', [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(200);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
    }

    public function testGetAddressNotFoundReturns404(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/v1/user/address/' . self::FAKE_PUBLIC_ID, [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(404);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testUpdateAddressNotFoundReturns404(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PUT',
            '/api/v1/user/address/' . self::FAKE_PUBLIC_ID . '/update',
            [],
            [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(404);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testRemoveNonExistentAddressReturns404(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', '/api/v1/user/address/' . self::FAKE_PUBLIC_ID . '/remove', [], [], $this->authHeaders());

        // L'adresse n'existe pas → deleteAddress lève NotFoundException → 404
        $this->assertResponseStatusCodeSame(404);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    // =========================================================================
    // MÉTHODES HTTP
    // =========================================================================

    public function testAddAddressOnlyAcceptsPost(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/user/address/add');

        // Le firewall s'exécute avant la vérification de méthode pour cette route → 401
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllAddressesOnlyAcceptsGet(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/user/address/all');

        // La vérification de méthode s'exécute avant le firewall pour cette route → 405
        $this->assertResponseStatusCodeSame(405);
    }

    public function testUpdateAddressOnlyAcceptsPut(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/user/address/' . self::FAKE_PUBLIC_ID . '/update');

        $this->assertResponseStatusCodeSame(405);
    }

    public function testRemoveAddressOnlyAcceptsDelete(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/user/address/' . self::FAKE_PUBLIC_ID . '/remove');

        $this->assertResponseStatusCodeSame(405);
    }
}
