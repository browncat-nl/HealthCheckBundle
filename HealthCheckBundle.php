<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle;

use Browncat\HealthCheckBundle\Check\HealthCheckInterface;
use Browncat\HealthCheckBundle\Checker\HealthCheckerInterface;
use Browncat\HealthCheckBundle\DependencyInjection\AddHealthCheckCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HealthCheckBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(HealthCheckerInterface::class)
            ->addTag('health_check.checker');

        $container->registerForAutoconfiguration(HealthCheckInterface::class)
            ->addTag('health_check.check');

        $container->addCompilerPass(new AddHealthCheckCompilerPass());
    }
}
