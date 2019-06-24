<?php

namespace Lamoda\AtolClientBundle\DependencyInjection;

use Lamoda\AtolClientBundle\AtolClientBundle;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $root = $builder->root('atol_client');

        $root
            ->beforeNormalization()
                ->ifTrue(function ($v) { return empty($v['clients']); })
                ->then(function ($v) { return ['clients' => ['default' => $v]]; })
            ->end()
            ->children()
                ->arrayNode('clients')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifTrue(function ($v) {
                                return isset($v['version'])
                                    && $v['version'] === AtolClientBundle::API_CLIENT_VERSION_3
                                    && empty($v['cash_register_group_code']);
                            })
                            ->thenInvalid('cash_register_group_code is required for Atol Api Client v3')
                        ->end()
                        ->children()
                            ->enumNode('version')
                                ->info('ATOL Api Version')
                                ->values([
                                    AtolClientBundle::API_CLIENT_VERSION_3,
                                    AtolClientBundle::API_CLIENT_VERSION_4,
                                ])
                                ->defaultValue(AtolClientBundle::API_CLIENT_VERSION_3)
                            ->end()
                            ->scalarNode('guzzle_client')
                                ->info('Guzzle client')
                                ->isRequired()
                            ->end()
                            ->arrayNode('guzzle_client_options')
                                ->info('Configuration options for guzzle client')
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('base_url')
                                ->info('Base url of ATOL service')
                                ->isRequired()
                            ->end()
                            ->scalarNode('cash_register_group_code')
                                ->info('ATOL\'s cash register group code (see docs)')
                            ->end()
                            ->scalarNode('callback_url')
                                ->info('ATOL\'s callback url (see docs)')
                                ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                ->end();

        return $builder;
    }
}
