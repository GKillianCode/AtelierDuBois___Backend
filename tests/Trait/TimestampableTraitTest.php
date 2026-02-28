<?php

namespace App\Tests\Trait;

use App\Trait\TimestampableTrait;
use PHPUnit\Framework\TestCase;

class TimestampableTraitTest extends TestCase
{
    private object $sut;

    protected function setUp(): void
    {
        $this->sut = new class {
            use TimestampableTrait;
        };
    }

    public function testSetAndGetCreatedAt(): void
    {
        $date = new \DateTime('2024-01-15 10:00:00');

        $this->sut->setCreatedAt($date);

        $this->assertSame($date, $this->sut->getCreatedAt());
    }

    public function testSetCreatedAtReturnsSelf(): void
    {
        $result = $this->sut->setCreatedAt(new \DateTime());

        $this->assertSame($this->sut, $result);
    }

    public function testSetAndGetUpdatedAt(): void
    {
        $date = new \DateTime('2024-06-20 15:30:00');

        $this->sut->setUpdatedAt($date);

        $this->assertSame($date, $this->sut->getUpdatedAt());
    }

    public function testSetUpdatedAtReturnsSelf(): void
    {
        $result = $this->sut->setUpdatedAt(new \DateTime());

        $this->assertSame($this->sut, $result);
    }

    public function testSetCreatedAtValueSetsDateTimeInstance(): void
    {
        $before = new \DateTime();
        $this->sut->setCreatedAtValue();
        $after = new \DateTime();

        $this->assertInstanceOf(\DateTimeInterface::class, $this->sut->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $this->sut->getCreatedAt());
        $this->assertLessThanOrEqual($after, $this->sut->getCreatedAt());
    }

    public function testSetUpdatedAtValueSetsDateTimeInstance(): void
    {
        $before = new \DateTime();
        $this->sut->setUpdatedAtValue();
        $after = new \DateTime();

        $this->assertInstanceOf(\DateTimeInterface::class, $this->sut->getUpdatedAt());
        $this->assertGreaterThanOrEqual($before, $this->sut->getUpdatedAt());
        $this->assertLessThanOrEqual($after, $this->sut->getUpdatedAt());
    }

    public function testCreatedAtAndUpdatedAtAreIndependent(): void
    {
        $created = new \DateTime('2024-01-01');
        $updated = new \DateTime('2024-12-31');

        $this->sut->setCreatedAt($created);
        $this->sut->setUpdatedAt($updated);

        $this->assertSame($created, $this->sut->getCreatedAt());
        $this->assertSame($updated, $this->sut->getUpdatedAt());
    }
}
