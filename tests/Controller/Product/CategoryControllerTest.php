<?php

namespace App\Tests\Controller\Product;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    // -------------------------------------------------------------------------
    // GET /api/public/v1/category/all
    // -------------------------------------------------------------------------

    public function testGetAllCategoriesReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/category/all');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetAllCategoriesResponseHasSuccessTrue(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/category/all');

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($body['success']);
    }

    public function testGetAllCategoriesResponseHasDataKey(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/category/all');

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $body);
    }

    public function testGetAllCategoriesIsPublicNoAuthRequired(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/public/v1/category/all');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetAllCategoriesOnlyAcceptsGet(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/public/v1/category/all');

        $this->assertResponseStatusCodeSame(405);
    }
}
