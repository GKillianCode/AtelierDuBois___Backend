<?php

namespace App\Tests\Trait;

use App\Trait\ValidateAndSaveTrait;
use App\Util\ValidatorUtil;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;

class ValidateAndSaveTraitTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private EntityManagerInterface&MockObject $entityManager;
    private object $sut;

    protected function setUp(): void
    {
        $this->logger        = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $validator     = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $validatorUtil = new ValidatorUtil($validator, $this->logger);

        $logger        = $this->logger;
        $entityManager = $this->entityManager;

        $this->sut = new class($logger, $validatorUtil, $entityManager) {
            use ValidateAndSaveTrait;

            public function __construct(
                private readonly LoggerInterface $logger,
                private readonly ValidatorUtil $validatorUtil,
                private readonly EntityManagerInterface $entityManager,
            ) {}
        };
    }

    // Object with no constraints → always valid
    private function validEntity(): object
    {
        return new \stdClass();
    }

    // Object with a NotBlank constraint on an empty field → triggers a violation
    private function invalidEntity(): object
    {
        return new class {
            #[\Symfony\Component\Validator\Constraints\NotBlank]
            public string $name = '';
        };
    }

    public function testValidateAndSaveCallsPersistAndFlushWhenNoViolations(): void
    {
        $entity = $this->validEntity();

        $this->entityManager->expects($this->once())->method('persist')->with($entity);
        $this->entityManager->expects($this->once())->method('flush');

        $this->sut->validateAndSave($entity);
    }

    public function testValidateAndSaveThrowsWhenViolationsExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation error:');

        $this->sut->validateAndSave($this->invalidEntity());
    }

    public function testValidateAndSaveDoesNotPersistWhenViolationsExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $this->sut->validateAndSave($this->invalidEntity());
    }

    public function testValidateAndSaveLogsErrorOnViolations(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->logger->expects($this->atLeastOnce())->method('error');

        $this->sut->validateAndSave($this->invalidEntity());
    }

    public function testValidateAndSaveViolationMessageContainsJsonEncodedErrors(): void
    {
        try {
            $this->sut->validateAndSave($this->invalidEntity());
            $this->fail('Expected InvalidArgumentException.');
        } catch (\InvalidArgumentException $e) {
            // The message must contain the violations serialized as JSON
            $this->assertStringContainsString('"property"', $e->getMessage());
            $this->assertStringContainsString('"message"', $e->getMessage());
        }
    }
}
