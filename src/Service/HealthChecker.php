<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Service;

use Browncat\HealthCheckBundle\Check\HealthCheck;

abstract class HealthChecker implements HealthCheckerInterface
{
    /** @var HealthCheck[] */
    private array $checks = [];

    public function addCheck(HealthCheck $check): self
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
