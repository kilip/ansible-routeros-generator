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

namespace RouterOS\Generator\Structure;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class MetaConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder('metas');

        $tree->getRootNode()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('name')->end()
                    ->scalarNode('package')->end()
                    ->scalarNode('command')->end()
                    ->scalarNode('type')->end()
                    ->arrayNode('keys')
                        ->scalarPrototype()->end()
                    ->end()
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
                    ->append($this->configurePropertiesNode('properties_override'))
                ->end()
            ->end();

        return $tree;
    }

    /**
     * @param string $nodeName
     *
     * @return ArrayNodeDefinition
     */
    public function configurePropertiesNode(string $nodeName)
    {
        $validTypes = ResourceProperty::getValidTypes();
        $validOptions = ResourceProperty::getValidOptions();

        $treeBuilder = new ArrayNodeDefinition($nodeName);
        $treeBuilder
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('name')->end()
                    ->scalarNode('original_name')->end()
                    ->enumNode('type')
                        ->values($validTypes)
                    ->end()
                    ->enumNode('elements')
                        ->values($validTypes)
                    ->end()
                    ->booleanNode('required')->end()
                    ->variableNode('default')->end()
                    ->arrayNode('choices')
                        ->scalarPrototype()->end()
                    ->end()
                    ->scalarNode('choice_type')->end()
                    ->scalarNode('description')->end()
                    ->arrayNode('options')
                        ->enumPrototype()
                            ->values($validOptions)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
