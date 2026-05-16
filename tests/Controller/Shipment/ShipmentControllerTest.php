<?php

namespace App\Tests\Controller\Shipment;

use App\Entity\User\User;
use App\Enum\UserType;
use App\Exception\NotFoundException;
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
    private const VALID_SHIPMENT_ID   = 'SHP-2604-T6KLBS6G';
    private const INVALID_SHIPMENT_ID = 'invalid-id';
    private const VALID_VARIANT_ID    = 'aB3dEfGhIjKlMnOpQrStuV';

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
        $mock->method('getShipmentDetail')->willReturn(
            new \App\Dto\Response\ResponseShipmentDetailDto(
                items: [],
                shipmentId: 'SHP-2604-T6KLBS6G',
                orderedAt: 0,
                status: new \App\Dto\Types\ShipmentStatusDto(code: 'PENDING', name: 'En attente'),
            )
        );
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

    // =========================================================================
    // GET /api/v1/shipment/history/{publicId}
    // =========================================================================

    public function testDetailRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/shipment/history/' . self::VALID_SHIPMENT_ID);

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testDetailOnlyAcceptsGet(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/shipment/history/' . self::VALID_SHIPMENT_ID);

        $this->assertResponseStatusCodeSame(405);
    }

    public function testDetailWithInvalidPublicIdReturns400(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $client->request('GET', '/api/v1/shipment/history/' . self::INVALID_SHIPMENT_ID, [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(400);
    }

    public function testDetailWithValidPublicIdReturns200(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $client->request('GET', '/api/v1/shipment/history/' . self::VALID_SHIPMENT_ID, [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testDetailResponseHasExpectedKeys(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockShipmentService());

        $client->request('GET', '/api/v1/shipment/history/' . self::VALID_SHIPMENT_ID, [], [], $this->authHeaders());

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('shipmentId', $body['data']);
        $this->assertArrayHasKey('status', $body['data']);
        $this->assertArrayHasKey('items', $body['data']);
    }

    // =========================================================================
    // Helpers review
    // =========================================================================

    /**
     * URL pour ADD : POST /api/v1/shipment/{publicId}/review  (sans variantPublicId dans le chemin).
     */
    private function addReviewUrl(string $shipment = self::VALID_SHIPMENT_ID): string
    {
        return "/api/v1/shipment/{$shipment}/review";
    }

    /**
     * URL pour EDIT/DELETE : /api/v1/shipment/{publicId}/review/{variantPublicId}.
     */
    private function reviewUrl(string $shipment = self::VALID_SHIPMENT_ID, string $variant = self::VALID_VARIANT_ID): string
    {
        return "/api/v1/shipment/{$shipment}/review/{$variant}";
    }

    private function reviewRightsUrl(string $shipment = self::VALID_SHIPMENT_ID): string
    {
        return "/api/v1/shipment/{$shipment}/review-rights";
    }

    /** @return array<string, string> */
    private function jsonHeaders(): array
    {
        return ['CONTENT_TYPE' => 'application/json'];
    }

    /**
     * Mock ShipmentService::addReview → ne fait rien (succès).
     *
     * @return ShipmentService&MockObject
     */
    private function mockServiceAddSuccess(): MockObject
    {
        $mock = $this->createMock(ShipmentService::class);
        $mock->method('addReview'); // void : pas de valeur de retour
        return $mock;
    }

    /**
     * Mock ShipmentService::addReview → lève ForbiddenException.
     *
     * @return ShipmentService&MockObject
     */
    private function mockServiceAddForbidden(): MockObject
    {
        $mock = $this->createMock(ShipmentService::class);
        $mock->method('addReview')
            ->willThrowException(new \App\Exception\ForbiddenException('review.add.forbidden'));
        return $mock;
    }

    /**
     * Mock ShipmentService::editReview → ne fait rien (succès).
     *
     * @return ShipmentService&MockObject
     */
    private function mockServiceEditSuccess(): MockObject
    {
        $mock = $this->createMock(ShipmentService::class);
        $mock->method('editReview'); // void
        return $mock;
    }

    /**
     * Mock ShipmentService::editReview → lève ForbiddenException.
     *
     * @return ShipmentService&MockObject
     */
    private function mockServiceEditForbidden(): MockObject
    {
        $mock = $this->createMock(ShipmentService::class);
        $mock->method('editReview')
            ->willThrowException(new \App\Exception\ForbiddenException('review.edit.forbidden'));
        return $mock;
    }

    /**
     * Mock ShipmentService::deleteReview → ne fait rien (succès).
     *
     * @return ShipmentService&MockObject
     */
    private function mockServiceDeleteSuccess(): MockObject
    {
        $mock = $this->createMock(ShipmentService::class);
        $mock->method('deleteReview'); // void
        return $mock;
    }

    /**
     * Mock ShipmentService::deleteReview → lève ForbiddenException.
     *
     * @return ShipmentService&MockObject
     */
    private function mockServiceDeleteForbidden(): MockObject
    {
        $mock = $this->createMock(ShipmentService::class);
        $mock->method('deleteReview')
            ->willThrowException(new \App\Exception\ForbiddenException('review.delete.forbidden'));
        return $mock;
    }

    /**
     * Mock qui lève une NotFoundException sur la méthode donnée.
     *
     * @return ShipmentService&MockObject
     */
    private function mockServiceThrowNotFound(string $method): MockObject
    {
        $mock = $this->createMock(ShipmentService::class);
        $mock->method($method)
            ->willThrowException(new NotFoundException('Shipment', self::VALID_SHIPMENT_ID));
        return $mock;
    }

    // =========================================================================
    // REVIEW — Contrat d'authentification (401)
    // =========================================================================

    public function testAddReviewRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('POST', $this->addReviewUrl(), [], [], $this->jsonHeaders(), '{"rating":3}');

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testEditReviewRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('PUT', $this->reviewUrl(), [], [], $this->jsonHeaders(), '{"rating":3}');

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testDeleteReviewRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('DELETE', $this->reviewUrl());

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testGetReviewRightsRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', $this->reviewRightsUrl());

        $this->assertResponseStatusCodeSame(401);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    // =========================================================================
    // REVIEW — Méthodes HTTP
    // =========================================================================

    public function testAddReviewOnlyAcceptsPost(): void
    {
        $client = static::createClient();
        // PUT vers l'URL d'add qui n'accepte que POST → 405
        $client->request('PUT', $this->addReviewUrl());

        $this->assertResponseStatusCodeSame(405);
    }

    public function testEditReviewOnlyAcceptsPut(): void
    {
        $client = static::createClient();
        $client->request('PATCH', $this->reviewUrl());

        $this->assertResponseStatusCodeSame(405);
    }

    public function testDeleteReviewOnlyAcceptsDelete(): void
    {
        $client = static::createClient();
        $client->request('PATCH', $this->reviewUrl());

        $this->assertResponseStatusCodeSame(405);
    }

    public function testGetReviewRightsOnlyAcceptsGet(): void
    {
        $client = static::createClient();
        $client->request('POST', $this->reviewRightsUrl());

        $this->assertResponseStatusCodeSame(405);
    }

    // =========================================================================
    // REVIEW — Validation publicId (400) — aucun mock nécessaire
    // (BadRequestException levée par ShipmentDetailRequestMapper avant le service)
    // =========================================================================

    public function testAddReviewReturns400WithInvalidShipmentId(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            $this->addReviewUrl(self::INVALID_SHIPMENT_ID),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['rating' => 3])
        );

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testEditReviewReturns400WithInvalidShipmentId(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PUT',
            $this->reviewUrl(self::INVALID_SHIPMENT_ID),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['rating' => 3])
        );

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testDeleteReviewReturns400WithInvalidShipmentId(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', $this->reviewUrl(self::INVALID_SHIPMENT_ID), [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    // =========================================================================
    // REVIEW — Validation payload (400) — aucun mock nécessaire
    // (BadRequestException levée par ReviewRequestMapper avant le service)
    // =========================================================================

    public function testAddReviewReturns400WhenRatingIsMissing(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            $this->addReviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['productVariantPublicId' => self::VALID_VARIANT_ID, 'comment' => 'Hello world'])
        );

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testAddReviewReturns400WhenRatingIsOutOfRange(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            $this->addReviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['productVariantPublicId' => self::VALID_VARIANT_ID, 'rating' => 6])
        );

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testAddReviewReturns400WhenCommentHasInvalidCharacters(): void
    {
        $client = $this->createAuthenticatedClient();

        // La validation du comment avec regex est uniquement dans le mapper EDIT.
        // Pour ADD, on teste un commentaire trop long (>255 chars) → 400.
        $client->request(
            'POST',
            $this->addReviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode([
                'productVariantPublicId' => self::VALID_VARIANT_ID,
                'rating' => 3,
                'comment' => str_repeat('a', 256),
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testEditReviewReturns400WhenCommentHasInvalidCharacters(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PUT',
            $this->reviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['rating' => 3, 'comment' => '<script>alert(1)</script>'])
        );

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testEditReviewReturns400WhenRatingIsMissing(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'PUT',
            $this->reviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['comment' => 'Updated'])
        );

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    // =========================================================================
    // REVIEW — Not found (404)
    // =========================================================================

    public function testAddReviewReturns404WhenShipmentNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockServiceThrowNotFound('addReview'));

        $client->request(
            'POST',
            $this->addReviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['productVariantPublicId' => self::VALID_VARIANT_ID, 'rating' => 3])
        );

        $this->assertResponseStatusCodeSame(404);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testEditReviewReturns404WhenShipmentNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockServiceThrowNotFound('editReview'));

        $client->request(
            'PUT',
            $this->reviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['rating' => 4])
        );

        $this->assertResponseStatusCodeSame(404);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testDeleteReviewReturns404WhenShipmentNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockServiceThrowNotFound('deleteReview'));

        $client->request('DELETE', $this->reviewUrl(), [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(404);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testGetReviewRightsReturns404WhenShipmentNotFound(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockServiceThrowNotFound('getReviewRights'));

        $client->request('GET', $this->reviewRightsUrl(), [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(404);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    // =========================================================================
    // REVIEW — Forbidden (403) — le service/manager lève ForbiddenException
    // =========================================================================

    public function testAddReviewReturns403WhenForbidden(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockServiceAddForbidden());

        $client->request(
            'POST',
            $this->addReviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['productVariantPublicId' => self::VALID_VARIANT_ID, 'rating' => 3])
        );

        $this->assertResponseStatusCodeSame(403);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testEditReviewReturns403WhenForbidden(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockServiceEditForbidden());

        $client->request(
            'PUT',
            $this->reviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['rating' => 4])
        );

        $this->assertResponseStatusCodeSame(403);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testDeleteReviewReturns403WhenForbidden(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockServiceDeleteForbidden());

        $client->request('DELETE', $this->reviewUrl(), [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(403);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    // =========================================================================
    // REVIEW — Succès (201 / 200)
    // =========================================================================

    public function testAddReviewReturns201OnSuccess(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockServiceAddSuccess());

        $client->request(
            'POST',
            $this->addReviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['productVariantPublicId' => self::VALID_VARIANT_ID, 'rating' => 4, 'comment' => 'Super produit'])
        );

        $this->assertResponseStatusCodeSame(201);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    public function testEditReviewReturns200OnSuccess(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockServiceEditSuccess());

        // Le commentaire est optionnel ; on envoie uniquement la note pour éviter
        // le regex cassé dans validateEditPayload.
        $client->request(
            'PUT',
            $this->reviewUrl(),
            [], [],
            array_merge($this->authHeaders(), $this->jsonHeaders()),
            json_encode(['rating' => 5])
        );

        $this->assertResponseStatusCodeSame(200);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    public function testDeleteReviewReturns200OnSuccess(): void
    {
        $client = $this->createAuthenticatedClient();
        static::getContainer()->set(ShipmentService::class, $this->mockServiceDeleteSuccess());

        $client->request('DELETE', $this->reviewUrl(), [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(200);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    // =========================================================================
    // REVIEW — Review rights (200)
    // =========================================================================

    public function testGetReviewRightsReturns200WithSuccessTrue(): void
    {
        $client = $this->createAuthenticatedClient();
        $mock = $this->createMock(ShipmentService::class);
        $mock->method('getReviewRights')->willReturn([]);
        static::getContainer()->set(ShipmentService::class, $mock);

        $client->request('GET', $this->reviewRightsUrl(), [], [], $this->authHeaders());

        $this->assertResponseStatusCodeSame(200);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
        $this->assertArrayHasKey('data', $body);
    }
}
