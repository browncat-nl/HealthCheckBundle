<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\DependencyInjection;

use Browncat\HealthCheckBundle\Checker\GlobalHealthChecker;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use function count;
use function in_array;
use function property_exists;

class AddHealthCheckCompilerPass implements CompilerPassInterface
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

            $healthCheckDefinition = $container->findDefinition($id);

            if ($healthCheckDefinition->getClass() === null) {
                continue;
            }

            $class = new ReflectionClass($healthCheckDefinition->getClass());

            // Check if 'bundle provided health check'
            if (
                $class->getNamespaceName() === 'Browncat\HealthCheckBundle\Check' ||
                $class->getShortName() === 'BrowncatVendorTestCheck' // Used in unit tests
            ) {
                // Health check written by bundle provider
                // Check if enabled in user config
                if ($healthCheckDefinition->hasTag('enabled')) {
                    $checkers = $healthCheckDefinition->getTag('checkers')[0];

                    foreach ($healthCheckerDefinitions as $definition) {
                        // Check if `checkers` array is not empty
                        // Class GlobalHealthChecker should have all checks so it's exlucded from this list.
                        if (count($checkers) > 0) {
                            if (in_array($definition->getClass(), $checkers) || $definition->getClass() === GlobalHealthChecker::class) {
                                $definition->addMethodCall('addCheck', [new Reference($id)]);
                            }
                        } else {
                            // `checkers` array is empty. We add the check to all checkers.
                            $definition->addMethodCall('addCheck', [new Reference($id)]);
                        }
                    }
                }
            } else {
                // Health check written by user
                foreach ($healthCheckerDefinitions as $definition) {
                    // Check if `public static $checkers` is set on class.
                    // Class GlobalHealthChecker should have all checks so it's exlucded from this list.
                    if (property_exists($healthCheckDefinition->getClass(), 'checkers') && $definition->getClass() !== GlobalHealthChecker::class) {
                        // Add checker if it exists in the $checkers array.
                        if (in_array($definition->getClass(), $class->getStaticPropertyValue('checkers'))) {
                            $definition->addMethodCall('addCheck', [new Reference($id)]);
                        }
                    } else {
                        // `public static $checkers` is not set. We add the check to all checkers.
                        $definition->addMethodCall('addCheck', [new Reference($id)]);
                    }
                }
            }
        }
    }
}
