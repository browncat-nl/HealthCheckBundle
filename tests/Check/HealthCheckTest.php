<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Tests\Check;

use Browncat\HealthCheckBundle\Check\HealthCheck;
use PHPUnit\Framework\TestCase;

use function is_null;

class HealthCheckTest extends TestCase
{
    public function testIsSkippedWhenSkippedIsSetToTrue(): void
    {
        $healthCheck = new HealthCheckMock(true, false);

        $this->assertTrue($healthCheck->isSkipped());
    }

    public function testIsNotSkippedWhenSkippedIsSetToFalse(): void
    {
        $healthCheck = new HealthCheckMock(false, false);

        $this->assertFalse($healthCheck->isSkipped());
    }

    public function testIsSucceededWhenSucceededIsSetToTrue(): void
    {
        $healthCheck = new HealthCheckMock(false, true);

        $this->assertTrue($healthCheck->isSucceeded());
    }

    public function testIsNotSucceededWhenSucceededIsSetToFalse(): void
    {
        $healhCheck = new HealthCheckMock(false, false);

        $this->assertFalse($healhCheck->isSucceeded());
    }

    public function testGetName(): void
    {
        $healthCheck = new HealthCheckMock(true, false, 'test:name');

        $this->assertEquals('test:name', $healthCheck->getName());
    }

    public function testGetNameWhenNotSet(): void
    {
        $healthCheck = new HealthCheckMock(false, false, null);

        $this->assertEquals('HealthCheckMock', $healthCheck->getName());
    }

    public function testGetReasonPhrase(): void
    {
        $healthCheck = new HealthCheckMock(true, false, 'test:name', 'the reason');

        $this->assertEquals('the reason', $healthCheck->getReasonPhrase());
    }

    public function testGetReasonPhraseWhenNull(): void
    {
        $healthCheck = new HealthCheckMock(true, false);

        $this->assertNull($healthCheck->getReasonPhrase());
    }

    public function testIfSucceededIsNullIfSkipped(): void
    {
        $healthCheck = new HealthCheckMock(true, false);

        $this->assertNull($healthCheck->isSucceeded());
    }
}

class HealthCheckMock extends HealthCheck
{
    public function __construct(bool $skipped, bool $succeeded, ?string $name = 'test:mock', ?string $reasonPharse = null)
    {
        $this->skipped   = $skipped;
        $this->succeeded = $succeeded;
        if (! is_null($name)) {
            $this->name = $name;
        }

        $this->reasonPhrase = $reasonPharse;
    }
}
