<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\Tests\DependencyInjection;

use Browncat\HealthCheckBundle\Check\HealthCheck;
use Browncat\HealthCheckBundle\DependencyInjection\AddHealthCheckCompilerPass;
use Browncat\HealthCheckBundle\HealthCheckBundle;
use Browncat\HealthCheckBundle\Service\GlobalHealthChecker;
use Browncat\HealthCheckBundle\Service\HealthChecker;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function class_exists;

class AddHealthCheckCompilerPassTest extends TestCase
{
    public function testAddHealthCheckToHealthChecker(): void
    {
        $container = new ContainerBuilder();

        $healthChecker = $container
            ->register('one-healthchecker', HealthCheckerOne::class)
            ->setPublic(false)
            ->addTag('health_check.checker');

        $healthCheck = $container
            ->register('all-checkers-healthcheck', HealthCheckForAllCheckers::class)
            ->setPublic(false)
            ->addTag('health_check.check');

        (new AddHealthCheckCompilerPass())->process($container);

        $methodCalls = $healthChecker->getMethodCalls();

        $this->assertTrue(isset($methodCalls[0][1][0]), 'MethodCall not set.');
        $this->assertInstanceOf(Reference::class, $methodCalls[0][1][0]);
        $this->assertEquals($methodCalls[0][1][0], 'all-checkers-healthcheck');
    }

    public function testAddHealthCheckToMultipleCheckers(): void
    {
        $container = new ContainerBuilder();

        $healthCheckerOne = $container
            ->register('one-healthchecker', HealthCheckerOne::class)
            ->setPublic(false)
            ->addTag('health_check.checker');

        $healthCheckerTwo = $container
            ->register('two-healthchecker', HealthCheckerTwo::class)
            ->setPublic(false)
            ->addTag('health_check.checker');

        $healthCheck = $container
            ->register('all-checkers-healthcheck', HealthCheckForAllCheckers::class)
            ->setPublic(false)
            ->addTag('health_check.check');

        (new AddHealthCheckCompilerPass())->process($container);

        $methodCalls = $healthCheckerOne->getMethodCalls();
        $this->assertEquals($methodCalls[0][1][0], 'all-checkers-healthcheck');

        $methodCalls = $healthCheckerTwo->getMethodCalls();
        $this->assertEquals($methodCalls[0][1][0], 'all-checkers-healthcheck');
    }

    public function testAddHealthCheckToTwoCheckers(): void
    {
        $container = new ContainerBuilder();

        $healthCheckerOne = $container
            ->register('one-healthchecker', HealthCheckerOne::class)
            ->setPublic(false)
            ->addTag('health_check.checker');

        $healthCheckerTwo = $container
            ->register('two-healthchecker', HealthCheckerTwo::class)
            ->setPublic(false)
            ->addTag('health_check.checker');

        $healthCheckerThree = $container
            ->register('three-healthchecker', HealthCheckerThree::class)
            ->setPublic(false)
            ->addTag('health_check.checker');

        $healthCheck = $container
            ->register('one-and-two-healthcheck', HealthCheckForCheckerOneAndTwo::class)
            ->setPublic(false)
            ->addTag('health_check.check');

        (new AddHealthCheckCompilerPass())->process($container);

        $methodCalls = $healthCheckerOne->getMethodCalls();
        $this->assertEquals($methodCalls[0][1][0], 'one-and-two-healthcheck');

        $methodCalls = $healthCheckerTwo->getMethodCalls();
        $this->assertEquals($methodCalls[0][1][0], 'one-and-two-healthcheck');

        $methodCalls = $healthCheckerThree->getMethodCalls();
        $this->assertFalse(isset($methodCalls[0][1][0]), 'MethodCall should not be set on HealthCheckerThree.');
    }

    public function testAddHealthCheckToNoneCheckers(): void
    {
        $container = new ContainerBuilder();

        $healthCheck = $container
            ->register('none-healthcheck', HealthCheckForCheckerNone::class)
            ->setPublic(false)
            ->addTag('health_check.check');

        $healthCheckerOne = $container
            ->register('one-healthchecker', HealthCheckerOne::class)
            ->setPublic(false)
            ->addTag('health_check.checker');

        $healthCheckerTwo = $container
            ->register('two-healthchecker', HealthCheckerTwo::class)
            ->setPublic(false)
            ->addTag('health_check.checker');

        (new AddHealthCheckCompilerPass())->process($container);

        $methodCalls = $healthCheckerOne->getMethodCalls();
        $this->assertFalse(isset($methodCalls[0][1][0]), 'MethodCall should not be set on HealthCheckerThree.');

        $methodCalls = $healthCheckerTwo->getMethodCalls();
        $this->assertFalse(isset($methodCalls[0][1][0]), 'MethodCall should not be set on HealthCheckerThree.');
    }

    public function testAddMultipleHealthChecksToHealthChecker(): void
    {
        $container = new ContainerBuilder();

        $healthCheckerOne = $container
            ->register('one-healthcheck', HealthCheckForCheckerOne::class)
            ->setPublic(false)
            ->addTag('health_check.check');

        $healthCheckForOneAndTwo = $container
            ->register('one-and-two-healthcheck', HealthCheckForCheckerOneAndTwo::class)
            ->setPublic(false)
            ->addTag('health_check.check');

        $healthChecker = $container
            ->register('one-healthchecker', HealthCheckerOne::class)
            ->setPublic(false)
            ->addTag('health_check.checker');

        (new AddHealthCheckCompilerPass())->process($container);

        $methodCalls = $healthChecker->getMethodCalls();
        $this->assertCount(2, $methodCalls);
        $this->assertEquals('one-healthcheck', $methodCalls[0][1][0]);
        $this->assertEquals('one-and-two-healthcheck', $methodCalls[1][1][0]);
    }

    public function testIfGlobalHealtCheckerHasAllChecks(): void
    {
        if (! class_exists(GlobalHealthChecker::class)) {
            $this->markTestSkipped('GlobalHealthChecker class does not exist, skipping this test.');

            return;
        }

        $container = new ContainerBuilder();

        $healthCheckNone = $container
            ->register('none-healthcheck', HealthCheckForCheckerNone::class)
            ->setPublic(false)
            ->addTag('health_check.check');

        $healthCheckerOne = $container
            ->register('one-healthcheck', HealthCheckForCheckerOne::class)
            ->setPublic(false)
            ->addTag('health_check.check');

        $healthCheckerTwo = $container
            ->register('two-healthcheck', HealthCheckForCheckerTwo::class)
            ->setPublic(false)
            ->addTag('health_check.check');

        $globalHealthChecker = $container
            ->register('global-healthchecker', GlobalHealthChecker::class)
            ->setPublic(false)
            ->addTag('health_check.checker');

        (new AddHealthCheckCompilerPass())->process($container);

        $methodCalls = $globalHealthChecker->getMethodCalls();
        $this->assertCount(3, $methodCalls);
        $this->assertEquals('none-healthcheck', $methodCalls[0][1][0]);
        $this->assertEquals('one-healthcheck', $methodCalls[1][1][0]);
        $this->assertEquals('two-healthcheck', $methodCalls[2][1][0]);
    }

    public function testVendorHealthCheck(): void
    {
        $container = $this->loadContainerWithConfig([
            'health_check' => [
                'checks' => [
                    'browncat.vendor' => [],
                ],
            ],
        ], static function ($container): void {
            $container->register('health_check.check.browncat.vendor', BrowncatVendorTestCheck::class)
                ->setPublic(true)
                ->addTag('health_check.check');

            $container->register('one-healthchecker', HealthCheckerOne::class)
                ->setPublic(false)
                ->addTag('health_check.checker');

            $container->register('two-healthchecker', HealthCheckerTwo::class)
                ->setPublic(false)
                ->addTag('health_check.checker');
        });

        (new AddHealthCheckCompilerPass())->process($container);

        $methodCallsHealthCheckerOne = $container->getDefinition('one-healthchecker')->getMethodCalls();
        $methodCallsHealthCheckerTwo = $container->getDefinition('two-healthchecker')->getMethodCalls();

        $this->assertEquals('health_check.check.browncat.vendor', $methodCallsHealthCheckerOne[0][1][0]);
        $this->assertEquals('health_check.check.browncat.vendor', $methodCallsHealthCheckerTwo[0][1][0]);
    }

    public function testVendorHealthCheckSubsetOfCheckers(): void
    {
        $container = $this->loadContainerWithConfig([
            'health_check' => [
                'checks' => [
                    'browncat.vendor' => [
                        'checkers' => ['Browncat\HealthCheckBundle\Tests\DependencyInjection\HealthCheckerOne'],
                    ],
                ],
            ],
        ], static function ($container): void {
            $container->register('health_check.check.browncat.vendor', BrowncatVendorTestCheck::class)
                ->setPublic(true)
                ->addTag('health_check.check');

            $container->register('one-healthchecker', HealthCheckerOne::class)
                ->setPublic(false)
                ->addTag('health_check.checker');

            $container->register('two-healthchecker', HealthCheckerTwo::class)
                ->setPublic(false)
                ->addTag('health_check.checker');
        });

        (new AddHealthCheckCompilerPass())->process($container);

        $methodCallsHealthCheckerOne = $container->getDefinition('one-healthchecker')->getMethodCalls();
        $methodCallsHealthCheckerTwo = $container->getDefinition('two-healthchecker')->getMethodCalls();

        $this->assertEquals('health_check.check.browncat.vendor', $methodCallsHealthCheckerOne[0][1][0]);
        $this->assertEquals([], $methodCallsHealthCheckerTwo);
    }

    private function loadContainerWithConfig(array $configs, ?callable $callback = null): ContainerBuilder
    {
        $bundle    = new HealthCheckBundle();
        $extension = $bundle->getContainerExtension();
        $container = new ContainerBuilder();
        $container->set('logger', $this->createMock(NullLogger::class));

        $callback($container);

        $container->registerExtension($extension);
        $extension->load($configs, $container);

        return $container;
    }
}

class HealthCheckForAllCheckers extends HealthCheck
{
}
class HealthCheckForCheckerOne extends HealthCheck
{
    public static $checkers = [HealthCheckerOne::class];
}
class HealthCheckForCheckerTwo extends HealthCheck
{
    public static $checkers = [HealthCheckerTwo::class];
}
class HealthCheckForCheckerOneAndTwo extends HealthCheck
{
    public static $checkers = [HealthCheckerOne::class, HealthCheckerTwo::class];
}

class HealthCheckForCheckerNone extends HealthCheck
{
    public static $checkers = [];
}

class BrowncatVendorTestCheck extends HealthCheck
{
}

class HealthCheckerOne extends HealthChecker
{
}
class HealthCheckerTwo extends HealthChecker
{
}
class HealthCheckerThree extends HealthChecker
{
}
