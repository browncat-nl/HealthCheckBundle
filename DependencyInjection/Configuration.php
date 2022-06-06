<?php

declare(strict_types=1);

namespace Browncat\HealthCheckBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('health_checks');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('checks')
                    ->info('List ids of package provided health checks to enable them here.')
                    ->example(['doctrine.connection'])
                    ->useAttributeAsKey('id')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('checkers')
                                ->info('(Optional) by default all checkers run package provided health checks. Fill in the FQCN of checkers you want this check to be run by.')
                                ->example([
                                    'Browncat\HealthCheckBundle\Checker\LivenessChecker',
                                    'Browncat\HealthCheckBundle\Checker\ReadinessChecker',
                                ])
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
