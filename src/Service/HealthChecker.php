<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Service;

use Browncat\HealthCheckBundle\Check\HealthCheck;

abstract class HealthChecker implements HealthCheckerInterface
{
    /** @var HealthCheck[] */
    private $checks = [];

    /**
     * @return self
     */
    // phpcs:ignore
    public function addCheck(HealthCheck $check)
    {
        $this->checks[] = $check;

        return $this;
    }

    /**
     * @return HealthCheck[]
     */
    public function getChecks(): array
    {
        return $this->checks;
    }
}
