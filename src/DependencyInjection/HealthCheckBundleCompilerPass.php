<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\DependencyInjection;

use Browncat\HealthCheckBundle\Service\GlobalHealthChecker;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use function in_array;
use function property_exists;

class HealthCheckBundleCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var Definition[] $healthCheckerDefinitions */
        $healthCheckerDefinitions = [];

        $healthCheckerServices = $container->findTaggedServiceIds('health_check.checker');
        $healthCheckServices   = $container->findTaggedServiceIds('health_check.check');

        foreach ($healthCheckerServices as $id => $tags) {
            $tags; // Prevent unused variable

            $healthCheckerDefinitions[] = $container->findDefinition($id);
        }

        foreach ($healthCheckServices as $id => $tags) {
            $tags; // Prevent unused variable

            foreach ($healthCheckerDefinitions as $definition) {
                // Check if `public static $checkers` is set on class.
                // Class GlobalHealthChecker should have all checks so it's exlucded from this list.
                if (property_exists($id, 'checkers') && $definition->getClass() !== GlobalHealthChecker::class) {
                    $class = new ReflectionClass($id);
                    // Add checker if it exists in the $checkers array.
                    if (in_array($definition->getClass(), $class->getStaticPropertyValue('checkers'))) {
                        $definition->addMethodCall('addCheck', [new Reference($id)]);
                    }
                } else {
                    // `public static $checkers` is not set so we add it without extra logic.
                    $definition->addMethodCall('addCheck', [new Reference($id)]);
                }
            }
        }
    }
}
