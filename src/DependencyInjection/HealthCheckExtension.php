<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\DependencyInjection;

use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

use function count;

class HealthCheckExtension extends ConfigurableExtension
{
    /**
     * @param mixed[] $mergedConfigs
     */
    protected function loadInternal(array $config, ContainerBuilder $container): void
    {
        $xmlLoader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $xmlLoader->load('services.xml');

        // Load bundle provided checks
        foreach ($config['checks'] as $id => $options) {
            // Workaround: Add key of type string to checkers option before adding it as tag to the definition.
            // This is needed for the XmlDumper, it can't handle integers as key.
            for ($i = 0; $i < count($options['checkers']); $i++) {
                $options['checkers']['c' . $i] = $options['checkers'][$i];
                unset($options['checkers'][$i]);
            }

            try {
                $container
                    ->findDefinition('health_check.check.' . $id)
                        ->addTag('enabled')
                        ->addTag('checkers', $options['checkers']);
            } catch (ServiceNotFoundException $e) {
                throw new RuntimeException('[HealthCheckBundle] ' . $id . ' check does not exist! make sure it is typed correctly and included in the version you are using!', 0, $e);
            }
        }
    }
}
