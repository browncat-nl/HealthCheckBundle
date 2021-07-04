<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Check;

abstract class HealthCheck implements HealthCheckInterface
{
    protected static string $name = '';

    protected ?string $reasonPhrase = null;

    protected ?bool $succeeded = false;

    protected bool $skipped = false;

    public function getName(): string
    {
        return $this::$name;
    }

    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    public function isSucceeded(): ?bool
    {
        return $this->skipped ? null : $this->succeeded;
    }

    public function getReasonPhrase(): ?string
    {
        return $this->reasonPhrase;
    }
}
