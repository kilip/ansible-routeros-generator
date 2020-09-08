<?php

/*
 * This file is part of the RouterOS project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace RouterOS\Scraper;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('routeros');

        $root = $builder->getRootNode()->children();

        $this->configureScraperConfig($root);
        $root->end();

        return $builder;
    }

    private function configureScraperConfig(NodeBuilder $root)
    {
        $root
            ->arrayNode('pages')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('name')->end()
                        ->scalarNode('package')->end()
                        ->booleanNode('debug')->end()
                        ->booleanNode('validated')->end()
                        ->scalarNode('command')->end()
                        ->scalarNode('type')->end()
                        ->arrayNode('generator')
                            ->children()
                                ->scalarNode('url')->end()
                                ->arrayNode('table_index')
                                    ->beforeNormalization()
                                        ->castToArray()
                                    ->end()
                                    ->scalarPrototype()->end()
                                ->end()
                                ->arrayNode('ignores')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('properties')
                            ->useAttributeAsKey('name')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('original_name')->end()
                                    ->scalarNode('type')->end()
                                    ->booleanNode('required')->end()
                                    ->arrayNode('defaultValue')
                                        ->beforeNormalization()
                                            ->ifString()
                                            ->then(function ($v) {
                                                return [$v];
                                            })
                                        ->end()
                                        ->scalarPrototype()->end()
                                    ->end()
                                    ->arrayNode('choices')
                                        ->scalarPrototype()->end()
                                    ->end()
                                    ->scalarNode('choice_type')->end()
                                    ->scalarNode('description')->end()
                                    ->arrayNode('options')
                                        ->scalarPrototype()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function getRootName()
    {
        return 'routeros';
    }
}
