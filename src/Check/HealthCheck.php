<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Check;

abstract class HealthCheck implements HealthCheckInterface
{
    /** @var string */
    protected $name = '';

    /** @var string|null */
    protected $reasonPhrase = null;

    /** @var bool */
    protected $succeeded = false;

    /** @var bool */
    protected $skipped = false;

    public function getName(): string
    {
        // Return class name when no custom name is set.
        if ('' == $this->name) {
            $classNamePieces = explode('\\', get_class($this));
            return end($classNamePieces);
        }

        return $this->name == '' ? end(explode('/', get_class($this))) : $this->name;
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
