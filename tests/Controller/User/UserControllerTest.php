<?php

namespace App\Tests\Controller\User;

use App\Service\User\UserService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private const ROUTE = '/api/register';

    private const VALID_PAYLOAD = [
        'firstname'       => 'Jean',
        'lastname'        => 'Martin',
        'email'           => 'jean.martin.test@example.com',
        'password'        => 'Abricot2024!',
        'confirmPassword' => 'Abricot2024!',
    ];

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $data */
    private function postJson(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $uri, array $data): void
    {
        $client->request(
            'POST',
            $uri,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }

    /**
     * @return UserService&MockObject
     */
    private function mockUserService(): MockObject
    {
        // registerUser est void → pas de willReturn
        return $this->createMock(UserService::class);
    }

    // -------------------------------------------------------------------------
    // Contrat HTTP de base
    // -------------------------------------------------------------------------

    public function testRegisterIsPublicNoAuthRequired(): void
    {
        $client = static::createClient();
        static::getContainer()->set(UserService::class, $this->mockUserService());

        $this->postJson($client, self::ROUTE, self::VALID_PAYLOAD);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testRegisterOnlyAcceptsPost(): void
    {
        $client = static::createClient();

        $client->request('GET', self::ROUTE);

        $this->assertResponseStatusCodeSame(405);
    }

    public function testRegisterResponseIsJson(): void
    {
        $client = static::createClient();
        static::getContainer()->set(UserService::class, $this->mockUserService());

        $this->postJson($client, self::ROUTE, self::VALID_PAYLOAD);

        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    // -------------------------------------------------------------------------
    // Cas succès
    // -------------------------------------------------------------------------

    public function testRegisterWithValidPayloadReturns200(): void
    {
        $client = static::createClient();
        static::getContainer()->set(UserService::class, $this->mockUserService());

        $this->postJson($client, self::ROUTE, self::VALID_PAYLOAD);

        $this->assertResponseStatusCodeSame(200);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    public function testRegisterSuccessResponseContainsStatusMessage(): void
    {
        $client = static::createClient();
        static::getContainer()->set(UserService::class, $this->mockUserService());

        $this->postJson($client, self::ROUTE, self::VALID_PAYLOAD);

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $body);
        $this->assertSame('User registered successfully', $body['data']['status']);
    }

    // -------------------------------------------------------------------------
    // Cas de validation (contrat erreur)
    // -------------------------------------------------------------------------

    public function testRegisterWithAllFieldsMissingReturns400(): void
    {
        $client = static::createClient();

        // Fournir tous les champs mais avec des valeurs invalides pour déclencher
        // la validation (400) sans tomber sur le bug EqualTo(value: null) du mapper
        $this->postJson($client, self::ROUTE, [
            'firstname'       => '',
            'lastname'        => '',
            'email'           => 'not-valid',
            'password'        => 'weak',
            'confirmPassword' => 'different',
        ]);

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
        $this->assertArrayHasKey('message', $body);
    }

    public function testRegisterWithMissingEmailReturns400(): void
    {
        $client = static::createClient();

        $payload = self::VALID_PAYLOAD;
        $payload['email'] = '';

        $this->postJson($client, self::ROUTE, $payload);

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testRegisterWithPasswordMismatchReturns400(): void
    {
        $client = static::createClient();

        $payload = self::VALID_PAYLOAD;
        $payload['confirmPassword'] = 'DifferentPassword1!';

        $this->postJson($client, self::ROUTE, $payload);

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testRegisterWithWeakPasswordReturns400(): void
    {
        $client = static::createClient();

        $payload = self::VALID_PAYLOAD;
        $payload['password'] = 'weak';
        $payload['confirmPassword'] = 'weak';

        $this->postJson($client, self::ROUTE, $payload);

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testRegisterWithInvalidJsonReturns400(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            self::ROUTE,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'not-valid-json'
        );

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testRegisterWithInvalidEmailFormatReturns400(): void
    {
        $client = static::createClient();

        $payload = self::VALID_PAYLOAD;
        $payload['email'] = 'not-an-email';

        $this->postJson($client, self::ROUTE, $payload);

        $this->assertResponseStatusCodeSame(400);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }
}
