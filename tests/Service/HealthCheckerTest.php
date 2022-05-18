<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Tests\Service;

use Browncat\HealthCheckBundle\Check\HealthCheck;
use Browncat\HealthCheckBundle\Service\GlobalHealthChecker;
use PHPUnit\Framework\TestCase;

class HealthCheckerTest extends TestCase
{
    public function testCountWithZeroHealthChecks(): void
    {
        $healthChecker = new GlobalHealthChecker();

        $this->assertCount(0, $healthChecker->getChecks());
    }

    public function testCountWithTwoHealthChecks(): void
    {
        $healthChecker = new GlobalHealthChecker();
        $healthChecker
            ->addCheck(new HealthCheckMock(true, true))
            ->addCheck(new HealthCheckMock(true, true));

        $this->assertCount(2, $healthChecker->getChecks());
    }

    public function testCheckInstanceOf(): void
    {
        $healthChecker = new GlobalHealthChecker();
        $healthChecker
            ->addCheck(new HealthCheckMock(true, true))
            ->addCheck(new HealthCheckMock(true, true));

        $this->assertInstanceOf(HealthCheck::class, $healthChecker->getChecks()[0]);
    }
}

class HealthCheckMock extends HealthCheck
{
    public function __construct(bool $skipped, bool $succeeded, string $name = 'test:mock', ?string $reasonPharse = null)
    {
        $this->skipped      = $skipped;
        $this->succeeded    = $succeeded;
        $this->$name        = $name;
        $this->reasonPhrase = $reasonPharse;
    }
}
