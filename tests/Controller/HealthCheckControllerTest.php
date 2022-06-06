<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Tests\Controller;

use Browncat\HealthCheckBundle\Check\HealthCheck;
use Browncat\HealthCheckBundle\Controller\HealthCheckController;
use Browncat\HealthCheckBundle\Checker\GlobalHealthChecker;
use Browncat\HealthCheckBundle\Checker\LivenessChecker;
use Browncat\HealthCheckBundle\Checker\ReadinessChecker;
use Browncat\HealthCheckBundle\Checker\StartupChecker;
use DG\BypassFinals;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

class HealthCheckControllerTest extends TestCase
{
    protected function setUp(): void
    {
        BypassFinals::enable(); // Needed for mocking classes marked as final.
    }

    // Tests for checkLiveness
    public function testCheckLivenessResponseCodeWithNoChecksEnabled(): void
    {
        $livenessChecker = new LivenessChecker();

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkLiveness($livenessChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckLivenessResponseCodeWithOneSucceedingCheck(): void
    {
        $livenessChecker = new LivenessChecker();
        $livenessChecker
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkLiveness($livenessChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckLivenessResponseCodeWithOneFailingCheck(): void
    {
        $livenessChecker = new LivenessChecker();
        $livenessChecker
            ->addCheck(new HealthCheckMock(false, false));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkLiveness($livenessChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testCheckLivenessResponseCodeWithOneSkippingCheck(): void
    {
        $livenessChecker = new LivenessChecker();
        $livenessChecker
            ->addCheck(new HealthCheckMock(true, false));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkLiveness($livenessChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckLivenessResponseCodeWithOneFailingAndOneSucceedingCheck(): void
    {
        $livenessChecker = new LivenessChecker();
        $livenessChecker
            ->addCheck(new HealthCheckMock(false, false))
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkLiveness($livenessChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testCheckLivenessResponseCodeWithOneFailingOneSkippingAndOneSucceedingCheck(): void
    {
        $livenessChecker = new LivenessChecker();
        $livenessChecker
            ->addCheck(new HealthCheckMock(false, false))
            ->addCheck(new HealthCheckMock(true, false))
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkLiveness($livenessChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    // Tests for checkReadiness
    public function testCheckReadinessResponseCodeWithNoChecksEnabled(): void
    {
        $readinessChecker = new ReadinessChecker();

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkReadiness($readinessChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckReadinessResponseCodeWithOneSucceedingCheck(): void
    {
        $readinessChecker = new ReadinessChecker();
        $readinessChecker
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkReadiness($readinessChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckReadinessResponseCodeWithOneFailingCheck(): void
    {
        $readinessChecker = new ReadinessChecker();
        $readinessChecker
            ->addCheck(new HealthCheckMock(false, false));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkReadiness($readinessChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testCheckReadinessResponseCodeWithOneSkippingCheck(): void
    {
        $readinessChecker = new ReadinessChecker();
        $readinessChecker
            ->addCheck(new HealthCheckMock(true, false));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkReadiness($readinessChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckReadinessResponseCodeWithOneFailingAndOneSucceedingCheck(): void
    {
        $readinessChecker = new ReadinessChecker();
        $readinessChecker
            ->addCheck(new HealthCheckMock(false, false))
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkReadiness($readinessChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testCheckReadinessResponseCodeWithOneFailingOneSkippingAndOneSucceedingCheck(): void
    {
        $readinessChecker = new ReadinessChecker();
        $readinessChecker
            ->addCheck(new HealthCheckMock(false, false))
            ->addCheck(new HealthCheckMock(true, false))
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkReadiness($readinessChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    // Tests for checkStartup
    public function testCheckStartupResponseCodeWithNoChecksEnabled(): void
    {
        $startupChecker = new StartupChecker();

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkStartup($startupChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckStartupResponseCodeWithOneSucceedingCheck(): void
    {
        $startupChecker = new StartupChecker();
        $startupChecker
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkStartup($startupChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckStartupResponseCodeWithOneFailingCheck(): void
    {
        $startupChecker = new StartupChecker();
        $startupChecker
            ->addCheck(new HealthCheckMock(false, false));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkStartup($startupChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testCheckStartupResponseCodeWithOneSkippingCheck(): void
    {
        $startupChecker = new StartupChecker();
        $startupChecker
            ->addCheck(new HealthCheckMock(true, false));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkStartup($startupChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckStartupResponseCodeWithOneFailingAndOneSucceedingCheck(): void
    {
        $startupChecker = new StartupChecker();
        $startupChecker
            ->addCheck(new HealthCheckMock(false, false))
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkStartup($startupChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testCheckStartupResponseCodeWithOneFailingOneSkippingAndOneSucceedingCheck(): void
    {
        $startupChecker = new StartupChecker();
        $startupChecker
            ->addCheck(new HealthCheckMock(false, false))
            ->addCheck(new HealthCheckMock(true, false))
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->checkStartup($startupChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    // test Global overview
    public function testGlobalOverviewResponseCodeWithNoChecksEnabled(): void
    {
        $globalHealthChecker = new GlobalHealthChecker();

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($globalHealthChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGlobalOverviewResultWithNoChecksEnabled(): void
    {
        $globalHealthChecker = new GlobalHealthChecker();

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($globalHealthChecker);

        $this->assertEquals('{"totalChecks":0,"skippedChecks":0,"succeededChecks":0,"failedChecks":0,"checks":[]}', $response->getContent());
    }

    public function testGlobalOverviewResponseCodeWithOneSucceedingCheck(): void
    {
        $globalhealthChecker = new GlobalHealthChecker();
        $globalhealthChecker
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($globalhealthChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGlobalOverviewResultWithOneSucceedingCheck(): void
    {
        $globalhealthChecker = new GlobalHealthChecker();
        $globalhealthChecker
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($globalhealthChecker);

        $this->assertEquals('{"totalChecks":1,"skippedChecks":0,"succeededChecks":1,"failedChecks":0,"checks":[{"name":"test:mock","skipped":false,"succeeded":true,"reasonPhrase":null}]}', $response->getContent());
    }

    public function testGlobalOverviewResponseCodeWithOneFailingCheck(): void
    {
        $HealthOverChecker = new GlobalHealthChecker();
        $HealthOverChecker
            ->addCheck(new HealthCheckMock(false, false));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($HealthOverChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testGlobalOverviewResultWithOneFailingCheck(): void
    {
        $HealthOverChecker = new GlobalHealthChecker();
        $HealthOverChecker
            ->addCheck(new HealthCheckMock(false, false));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($HealthOverChecker);

        $this->assertEquals('{"totalChecks":1,"skippedChecks":0,"succeededChecks":0,"failedChecks":1,"checks":[{"name":"test:mock","skipped":false,"succeeded":false,"reasonPhrase":null}]}', $response->getContent());
    }

    public function testGlobalOverviewResponseCodeWithOneSkippingCheck(): void
    {
        $globalHealthChecker = new GlobalHealthChecker();
        $globalHealthChecker
            ->addCheck(new HealthCheckMock(true, false));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($globalHealthChecker);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGlobalOverviewResultWithOneSkippingCheck(): void
    {
        $globalHealthChecker = new GlobalHealthChecker();
        $globalHealthChecker
            ->addCheck(new HealthCheckMock(true, false));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($globalHealthChecker);

        $this->assertEquals('{"totalChecks":1,"skippedChecks":1,"succeededChecks":0,"failedChecks":0,"checks":[{"name":"test:mock","skipped":true,"succeeded":null,"reasonPhrase":null}]}', $response->getContent());
    }

    public function testGlobalOverviewResponseCodeWithOneFailingAndOneSucceedingCheck(): void
    {
        $globalHealthChecker = new GlobalHealthChecker();
        $globalHealthChecker
            ->addCheck(new HealthCheckMock(false, false))
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($globalHealthChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testGlobalOverviewResultCodeWithOneFailingAndOneSucceedingCheck(): void
    {
        $globalHealthChecker = new GlobalHealthChecker();
        $globalHealthChecker
            ->addCheck(new HealthCheckMock(false, false))
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($globalHealthChecker);

        $this->assertEquals('{"totalChecks":2,"skippedChecks":0,"succeededChecks":1,"failedChecks":1,"checks":[{"name":"test:mock","skipped":false,"succeeded":false,"reasonPhrase":null},{"name":"test:mock","skipped":false,"succeeded":true,"reasonPhrase":null}]}', $response->getContent());
    }

    public function testGlobalOverviewResponseCodeWithOneFailingOneSkippingAndOneSucceedingCheck(): void
    {
        $globalHealthChecker = new GlobalHealthChecker();
        $globalHealthChecker
            ->addCheck(new HealthCheckMock(false, false))
            ->addCheck(new HealthCheckMock(true, false))
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($globalHealthChecker);

        $this->assertEquals(503, $response->getStatusCode());
    }

    public function testGlobalOverviewResultWithOneFailingOneSkippingAndOneSucceedingCheck(): void
    {
        $globalHealthChecker = new GlobalHealthChecker();
        $globalHealthChecker
            ->addCheck(new HealthCheckMock(false, false))
            ->addCheck(new HealthCheckMock(true, false))
            ->addCheck(new HealthCheckMock(false, true));

        $healthCheckController = new HealthCheckController($this->createMock(AbstractLogger::class));
        $response              = $healthCheckController->healthOverview($globalHealthChecker);

        $this->assertEquals('{"totalChecks":3,"skippedChecks":1,"succeededChecks":1,"failedChecks":1,"checks":[{"name":"test:mock","skipped":false,"succeeded":false,"reasonPhrase":null},{"name":"test:mock","skipped":true,"succeeded":null,"reasonPhrase":null},{"name":"test:mock","skipped":false,"succeeded":true,"reasonPhrase":null}]}', $response->getContent());
    }
}

class HealthCheckMock extends HealthCheck
{
    public function __construct(bool $skipped, bool $succeeded, string $name = 'test:mock', ?string $reasonPharse = null)
    {
        $this->skipped      = $skipped;
        $this->succeeded    = $succeeded;
        $this->name         = $name;
        $this->reasonPhrase = $reasonPharse;
    }
}
