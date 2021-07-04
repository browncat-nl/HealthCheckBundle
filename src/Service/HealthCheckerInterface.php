<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Service;

use Browncat\HealthCheckBundle\Check\HealthCheck;

interface HealthCheckerInterface
{
    public function addCheck(HealthCheck $check): void;

    /**
     * @return HealthCheck[]
     */
    public function getChecks(): array;
}
