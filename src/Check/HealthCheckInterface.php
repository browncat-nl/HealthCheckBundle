<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Check;

interface HealthCheckInterface
{
    /**
     * @return string Name of check.
     */
    public function getName(): string;

    /**
     * @return bool Wether the check has been skipped.
     */
    public function isSkipped(): bool;

    /**
     * @return bool|null Wether the check was sucessfull. Returns null if the check was skipped.
     */
    public function isSucceeded(): ?bool;

    /**
     * @return string|null Reason for the failure or success.
     */
    public function getReasonPhrase(): ?string;
}
