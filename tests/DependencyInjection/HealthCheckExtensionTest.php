<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Tests\DependencyInjection;

use Browncat\HealthCheckBundle\Check\DoctrineConnectionHealthCheck;
use Browncat\HealthCheckBundle\Checker\GlobalHealthChecker;
use Browncat\HealthCheckBundle\Checker\LivenessChecker;
use Browncat\HealthCheckBundle\Checker\ReadinessChecker;
use Browncat\HealthCheckBundle\Checker\StartupChecker;
use Browncat\HealthCheckBundle\Controller\HealthCheckController;
use Browncat\HealthCheckBundle\HealthCheckBundle;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

class HealthCheckExtensionTest extends TestCase
{
    public function testIfDoctrineConnectionHealthCheckIsLoaded(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $this->assertTrue($container->has(DoctrineConnectionHealthCheck::class));
    }

    public function testDoctrineConnectionHealthCheckArgumentBehaviour(): void
    {
        $container = $this->buildContainerWithConfig([]);

        $definition = $container->getDefinition(DoctrineConnectionHealthCheck::class);

        /** @var Reference */
        $doctrineReference = $definition->getArgument(0);

        $this->assertEquals('doctrine', $doctrineReference->__toString());
        $this->assertEquals(ContainerInterface::IGNORE_ON_INVALID_REFERENCE, $doctrineReference->getInvalidBehavior());
    }

    public function testIfGlobalHealthCheckerDefinitionIsPrivate(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $this->assertFalse($container->getDefinition(GlobalHealthChecker::class)->isPublic());
    }

    public function testIfGlobalHealthCheckerAliasIsPublic(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $this->assertTrue($container->getAlias('health_check.checker.global')->isPublic());
    }

    public function testIfLivenessCheckerDefinitionIsPrivate(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $this->assertFalse($container->getDefinition(LivenessChecker::class)->isPublic());
    }

    public function testIfLivenessCheckerAliasIsPublic(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $this->assertTrue($container->getAlias('health_check.checker.liveness')->isPublic());
    }

    public function testIfStartupCheckerDefinitionIsPrivate(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $this->assertFalse($container->getDefinition(StartupChecker::class)->isPublic());
    }

    public function testIfStartupCheckerAliasIsPublic(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $this->assertTrue($container->getAlias('health_check.checker.startup')->isPublic());
    }

    public function testIfReadinessCheckerDefinitionIsPrivate(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $this->assertFalse($container->getDefinition(ReadinessChecker::class)->isPublic());
    }

    public function testIfReadinessCheckerAliasIsPublic(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $this->assertTrue($container->getAlias('health_check.checker.readiness')->isPublic());
    }

    public function testRetrieveCheckersByTag(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $services  = $container->findTaggedServiceIds('health_check.checker');

        $this->assertArrayHasKey(GlobalHealthChecker::class, $services);
        $this->assertArrayHasKey(LivenessChecker::class, $services);
        $this->assertArrayHasKey(StartupChecker::class, $services);
        $this->assertArrayHasKey(ReadinessChecker::class, $services);
    }

    public function testCountCheckersRetrievedByTag(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $services  = $container->findTaggedServiceIds('health_check.checker');

        $this->assertCount(4, $services);
    }

    public function testRrieveChecksByTag(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $services  = $container->findTaggedServiceIds('health_check.check');

        $this->assertArrayHasKey(DoctrineConnectionHealthCheck::class, $services);
    }

    public function testCountChecksRetrievedByTag(): void
    {
        $container = $this->buildContainerWithConfig([]);
        $services  = $container->findTaggedServiceIds('health_check.check');

        $this->assertCount(1, $services);
    }

    public function testIfHealthCheckControllerIsLoaded(): void
    {
        $container = $this->buildContainerWithConfig([]);

        $this->assertTrue($container->has(HealthCheckController::class));
    }

    public function testHealthCheckControllerArgumentBehaviour(): void
    {
        $container = $this->buildContainerWithConfig([]);

        $this->assertTrue($container->has(HealthCheckController::class));

        $definition = $container->getDefinition(HealthCheckController::class);

        /** @var Reference */
        $loggerReference = $definition->getArgument(0);

        $this->assertEquals('logger', $loggerReference->__toString());
        $this->assertEquals(ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $loggerReference->getInvalidBehavior());
    }

    public function testVendorHealthCheckNonExisting(): void
    {
        try {
            $this->buildContainerWithConfig([
                'health_check' => [
                    'checks' => [
                        'nonexisting.check' => [],
                    ],
                ],
            ]);
        } catch (RuntimeException $e) {
            $this->assertEquals('[HealthCheckBundle] nonexisting.check check does not exist! make sure it is typed correctly and included in the version you are using!', $e->getMessage());

            return;
        }

        $this->fail('Config with non existing check must throw RunetimeException');
    }

    public function testVendorHealthCheck(): void
    {
        $container = $this->buildContainerWithConfig([
            'health_check' => [
                'checks' => [
                    'doctrine.connection' => [],
                ],
            ],
        ]);

        $definition = $container->findDefinition('health_check.check.doctrine.connection');
        $this->assertTrue($definition->hasTag('enabled'));
        $this->assertCount(0, $definition->getTag('checkers')[0]);
    }

    public function testVendorHealthCheckWhomIsEnabledForSubsetOfCheckers(): void
    {
        $container = $this->buildContainerWithConfig([
            'health_check' => [
                'checks' => [
                    'doctrine.connection' => [
                        'checkers' => [
                            'Browncat\HealthCheckBundle\Checker\LivenessChecker',
                            'Browncat\HealthCheckBundle\Checker\ReadinessChecker',
                        ],
                    ],
                ],
            ],
        ]);

        $definition = $container->findDefinition('health_check.check.doctrine.connection');
        $this->assertTrue($definition->hasTag('enabled'));
        $this->assertCount(2, $definition->getTag('checkers')[0]);
    }

    private function buildContainerWithConfig(array $configs)
    {
        $bundle    = new HealthCheckBundle();
        $extension = $bundle->getContainerExtension();
        $container = new ContainerBuilder();
        $container->set('logger', $this->createMock(AbstractLogger::class));

        $container->registerExtension($extension);
        $extension->load($configs, $container);

        $bundle->build($container);

        return $container;
    }
}
