<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Tests\DependencyInjection;

use Browncat\HealthCheckBundle\HealthCheckBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigurationTest extends TestCase
{
    private function getContainer(array $configs = [])
    {
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', true);
        $container->setParameter('kernel.bundles', ['HealthCheckBundle' => 'Browncat\HealthCheckBundle\HealthCheckBundle']);

        $bundle = new HealthCheckBundle();

        $extension = $bundle->getContainerExtension();
        $extension->load($configs, $container);

        return $container;
    }

    public function testEmptyConfig(): void
    {
        $container = $this->getContainer();

        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }

    public function testMalformedConfig(): void
    {
        try {
            $this->getContainer([
                'health_check' => ['checkx' => 'test'],
            ]);
        } catch (InvalidConfigurationException $e) {
            $this->assertEquals('Unrecognized option "checkx" under "health_checks". Did you mean "checks"?', $e->getMessage());

            return;
        }

        $this->fail('Malformed config must throw InvalidConfigurationException');
    }
}
