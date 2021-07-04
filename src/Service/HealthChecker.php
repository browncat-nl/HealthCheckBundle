<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Service;

use Browncat\HealthCheckBundle\Check\HealthCheck;

abstract class HealthChecker
{
    /** @var HealthCheck[] */
    private array $checks = [];

    public function addCheck(HealthCheck $check): void
    {
        $this->checks[] = $check;
    }

    /**
     * @return HealthCheck[]
     */
    public function getChecks(): array
    {
        return $this->checks;
    }
}
