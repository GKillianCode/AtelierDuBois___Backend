<?php

namespace App\Tests\Controller\Product;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    // 22 chars → utilisé pour /product/{publicId}
    private const FAKE_PUBLIC_ID = 'aB3dEfGhIjKlMnOpQrStuV';
    // 22 chars → validation UUID base62 requiert exactement 22 caractères
    private const REVIEW_PUBLIC_ID = 'aB3dEfGhIjKlMnOpQrStuV';

    // -------------------------------------------------------------------------
    // GET /api/public/v1/product/all
    // -------------------------------------------------------------------------

    public function testGetAllProductsReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/product/all');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetAllProductsResponseHasSuccessTrue(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/product/all');

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    public function testGetAllProductsIsPublicNoAuthRequired(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/product/all');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetAllProductsOnlyAcceptsGet(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/public/v1/product/all');

        $this->assertResponseStatusCodeSame(405);
    }

    // -------------------------------------------------------------------------
    // GET /api/public/v1/product/{publicId}
    // Route publique — variante inexistante → 404 (comportement réel)
    // -------------------------------------------------------------------------

    public function testGetProductByPublicIdNotFoundReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/product/' . self::FAKE_PUBLIC_ID);

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetProductByPublicIdNotFoundResponseHasSuccessFalse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/product/' . self::FAKE_PUBLIC_ID);

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testGetProductByPublicIdIsPublicNoAuthRequired(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/product/' . self::FAKE_PUBLIC_ID);

        // Variante inexistante → 404, mais pas 401 : la route est bien publique
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetProductByPublicIdOnlyAcceptsGet(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/public/v1/product/' . self::FAKE_PUBLIC_ID);

        $this->assertResponseStatusCodeSame(405);
    }

    // -------------------------------------------------------------------------
    // GET /api/public/v1/product/{publicId}/reviews
    // Route publique — variante inexistante → 404 (comportement réel)
    // -------------------------------------------------------------------------

    public function testGetReviewsByVariantNotFoundReturns404(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/product/' . self::REVIEW_PUBLIC_ID . '/reviews');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetReviewsByVariantNotFoundResponseHasSuccessFalse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/product/' . self::REVIEW_PUBLIC_ID . '/reviews');

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($body['success']);
    }

    public function testGetReviewsByVariantIsPublicNoAuthRequired(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/product/' . self::REVIEW_PUBLIC_ID . '/reviews');

        // Variante inexistante → 404, mais pas 401 : la route est bien publique
        $this->assertResponseStatusCodeSame(404);
    }
}
