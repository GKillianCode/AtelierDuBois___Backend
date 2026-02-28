<?php

namespace App\Tests\Utils;

use App\Util\ValidatorUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Validation;

class ValidatorUtilsTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private ValidatorUtil $sut;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $this->sut = new ValidatorUtil($validator, $this->logger);
    }

    // Object with no constraints → always valid
    private function validObject(): object
    {
        return new \stdClass();
    }

    // Object whose $name is blank, violating #[NotBlank] and #[Length(min: 2)]
    private function invalidObject(): object
    {
        return new class {
            #[NotBlank]
            #[Length(min: 2)]
            public string $name = '';
        };
    }

    // =========================================================================
    // validate()
    // =========================================================================

    public function testValidateReturnsEmptyListForValidObject(): void
    {
        $violations = $this->sut->validate($this->validObject());

        $this->assertCount(0, $violations);
    }

    public function testValidateReturnsViolationsForInvalidObject(): void
    {
        $violations = $this->sut->validate($this->invalidObject());

        $this->assertGreaterThan(0, count($violations));
    }

    public function testValidateViolationContainsPropertyPath(): void
    {
        $violations = $this->sut->validate($this->invalidObject());

        $this->assertSame('name', $violations[0]->getPropertyPath());
    }

    // =========================================================================
    // hasViolations()
    // =========================================================================

    public function testHasViolationsReturnsFalseForValidObject(): void
    {
        $this->assertFalse($this->sut->hasViolations($this->validObject()));
    }

    public function testHasViolationsReturnsTrueForInvalidObject(): void
    {
        $this->assertTrue($this->sut->hasViolations($this->invalidObject()));
    }

    // =========================================================================
    // getViolationsAsArray()
    // =========================================================================

    public function testGetViolationsAsArrayReturnsEmptyArrayForValidObject(): void
    {
        $result = $this->sut->getViolationsAsArray($this->validObject());

        $this->assertSame([], $result);
    }

    public function testGetViolationsAsArrayReturnsArrayForInvalidObject(): void
    {
        $result = $this->sut->getViolationsAsArray($this->invalidObject());

        $this->assertNotEmpty($result);
    }

    public function testGetViolationsAsArrayEntryHasPropertyKey(): void
    {
        $result = $this->sut->getViolationsAsArray($this->invalidObject());

        $this->assertArrayHasKey('property', $result[0]);
    }

    public function testGetViolationsAsArrayEntryHasMessageKey(): void
    {
        $result = $this->sut->getViolationsAsArray($this->invalidObject());

        $this->assertArrayHasKey('message', $result[0]);
    }

    public function testGetViolationsAsArrayPropertyMatchesConstrainedField(): void
    {
        $result = $this->sut->getViolationsAsArray($this->invalidObject());

        $this->assertSame('name', $result[0]['property']);
    }

    public function testGetViolationsAsArrayReturnsOneEntryPerViolation(): void
    {
        // #[NotBlank] + #[Length(min:2)] both violated on an empty string → 2 violations
        $result = $this->sut->getViolationsAsArray($this->invalidObject());

        $this->assertCount(2, $result);
    }
}
