<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Checker;

use Browncat\HealthCheckBundle\Check\HealthCheck;

interface HealthCheckerInterface
{
    /**
     * @return self
     */
    // phpcs:ignore
    public function addCheck(HealthCheck $check);

    /**
     * @return HealthCheck[]
     */
    public function getChecks(): array;
}
