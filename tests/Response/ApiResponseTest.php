<?php

namespace App\Tests\Response;

use App\Response\ApiResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseTest extends TestCase
{
    /** @return array<string, mixed> */
    private function decode(ApiResponse $response): array
    {
        return json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }

    // =========================================================================
    // success()
    // =========================================================================

    public function testSuccessHasTrueSuccessField(): void
    {
        $body = $this->decode(ApiResponse::success());

        $this->assertTrue($body['success']);
    }

    public function testSuccessWithoutDataOmitsDataField(): void
    {
        $body = $this->decode(ApiResponse::success());

        $this->assertArrayNotHasKey('data', $body);
    }

    public function testSuccessWithDataExposesDataField(): void
    {
        $body = $this->decode(ApiResponse::success(['id' => 42]));

        $this->assertSame(['id' => 42], $body['data']);
    }

    public function testSuccessWithoutMessageOmitsMessageField(): void
    {
        $body = $this->decode(ApiResponse::success());

        $this->assertArrayNotHasKey('message', $body);
    }

    public function testSuccessWithMessageExposesMessageField(): void
    {
        $body = $this->decode(ApiResponse::success(null, 'Done'));

        $this->assertSame('Done', $body['message']);
    }

    // =========================================================================
    // created()
    // =========================================================================

    public function testCreatedHasTrueSuccessField(): void
    {
        $body = $this->decode(ApiResponse::created());

        $this->assertTrue($body['success']);
    }

    public function testCreatedHasDefaultMessage(): void
    {
        $body = $this->decode(ApiResponse::created());

        $this->assertSame('Resource created', $body['message']);
    }

    public function testCreatedWithDataExposesDataField(): void
    {
        $body = $this->decode(ApiResponse::created(['uuid' => 'abc']));

        $this->assertSame(['uuid' => 'abc'], $body['data']);
    }

    // =========================================================================
    // error()
    // =========================================================================

    public function testErrorHasFalseSuccessField(): void
    {
        $body = $this->decode(ApiResponse::error('Something went wrong'));

        $this->assertFalse($body['success']);
    }

    public function testErrorExposesMessage(): void
    {
        $body = $this->decode(ApiResponse::error('Something went wrong'));

        $this->assertSame('Something went wrong', $body['message']);
    }

    public function testErrorWithoutErrorsOmitsDataField(): void
    {
        $body = $this->decode(ApiResponse::error('Oops'));

        $this->assertArrayNotHasKey('data', $body);
    }

    public function testErrorWithErrorsExposesErrorsUnderDataField(): void
    {
        $body = $this->decode(ApiResponse::error('Oops', ['field' => 'required']));

        $this->assertSame(['field' => 'required'], $body['data']['errors']);
    }

    // =========================================================================
    // notFound()
    // =========================================================================

    public function testNotFoundHasFalseSuccessField(): void
    {
        $body = $this->decode(ApiResponse::notFound());

        $this->assertFalse($body['success']);
    }

    public function testNotFoundHasDefaultMessage(): void
    {
        $body = $this->decode(ApiResponse::notFound());

        $this->assertSame('Resource not found', $body['message']);
    }

    public function testNotFoundWithCustomMessageExposesIt(): void
    {
        $body = $this->decode(ApiResponse::notFound('User not found'));

        $this->assertSame('User not found', $body['message']);
    }

    public function testNotFoundWithoutDataOmitsDataField(): void
    {
        $body = $this->decode(ApiResponse::notFound());

        $this->assertArrayNotHasKey('data', $body);
    }

    // =========================================================================
    // unauthorized()
    // =========================================================================

    public function testUnauthorizedHasFalseSuccessField(): void
    {
        $body = $this->decode(ApiResponse::unauthorized());

        $this->assertFalse($body['success']);
    }

    public function testUnauthorizedHasDefaultMessage(): void
    {
        $body = $this->decode(ApiResponse::unauthorized());

        $this->assertSame('Unauthorized', $body['message']);
    }

    public function testUnauthorizedOmitsDataField(): void
    {
        $body = $this->decode(ApiResponse::unauthorized());

        $this->assertArrayNotHasKey('data', $body);
    }

    // =========================================================================
    // forbidden()
    // =========================================================================

    public function testForbiddenHasFalseSuccessField(): void
    {
        $body = $this->decode(ApiResponse::forbidden());

        $this->assertFalse($body['success']);
    }

    public function testForbiddenHasDefaultMessage(): void
    {
        $body = $this->decode(ApiResponse::forbidden());

        $this->assertSame('Forbidden', $body['message']);
    }

    public function testForbiddenOmitsDataField(): void
    {
        $body = $this->decode(ApiResponse::forbidden());

        $this->assertArrayNotHasKey('data', $body);
    }

    // =========================================================================
    // validationError()
    // =========================================================================

    public function testValidationErrorHasFalseSuccessField(): void
    {
        $body = $this->decode(ApiResponse::validationError([]));

        $this->assertFalse($body['success']);
    }

    public function testValidationErrorExposesViolationsUnderDataField(): void
    {
        $violations = [['property' => 'email', 'message' => 'Invalid email.']];

        $body = $this->decode(ApiResponse::validationError($violations));

        $this->assertSame($violations, $body['data']['violations']);
    }

    public function testValidationErrorHasDefaultMessage(): void
    {
        $body = $this->decode(ApiResponse::validationError([]));

        $this->assertSame('Validation failed', $body['message']);
    }

    // =========================================================================
    // conflict()
    // =========================================================================

    public function testConflictHasFalseSuccessField(): void
    {
        $body = $this->decode(ApiResponse::conflict('Email already taken'));

        $this->assertFalse($body['success']);
    }

    public function testConflictExposesMessage(): void
    {
        $body = $this->decode(ApiResponse::conflict('Email already taken'));

        $this->assertSame('Email already taken', $body['message']);
    }

    public function testConflictWithoutDataOmitsDataField(): void
    {
        $body = $this->decode(ApiResponse::conflict('Conflict'));

        $this->assertArrayNotHasKey('data', $body);
    }

    // =========================================================================
    // serverError()
    // =========================================================================

    public function testServerErrorHasFalseSuccessField(): void
    {
        $body = $this->decode(ApiResponse::serverError());

        $this->assertFalse($body['success']);
    }

    public function testServerErrorHasDefaultMessage(): void
    {
        $body = $this->decode(ApiResponse::serverError());

        $this->assertSame('Internal server error', $body['message']);
    }

    public function testServerErrorOmitsDataField(): void
    {
        $body = $this->decode(ApiResponse::serverError());

        $this->assertArrayNotHasKey('data', $body);
    }

    // =========================================================================
    // Content-Type
    // =========================================================================

    public function testResponseContentTypeIsJson(): void
    {
        $response = ApiResponse::success();

        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
    }

    // =========================================================================
    // HTTP status codes (only non-obvious ones)
    // =========================================================================

    public function testCreatedReturns201(): void
    {
        $this->assertSame(Response::HTTP_CREATED, ApiResponse::created()->getStatusCode());
    }

    public function testValidationErrorReturns422(): void
    {
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, ApiResponse::validationError([])->getStatusCode());
    }

    public function testSuccessSupportsCustomStatusCode(): void
    {
        $this->assertSame(Response::HTTP_ACCEPTED, ApiResponse::success(null, null, Response::HTTP_ACCEPTED)->getStatusCode());
    }
}
