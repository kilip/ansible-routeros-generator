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

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class MetaConfiguration implements ConfigurationInterface
{
    use ResourcePropertiesConfigurationTrait;

    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder('meta');

        $root = $tree->getRootNode()->children();

        $root = $root
            ->arrayNode('metas')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children(); // start array proto typing

        $root = $this->configureGeneral($root);
        $root = $this->configurePropertiesNode($root, 'properties_override');

        $root
                    ->end() // end array prototyping
                ->end()
            ->end();

        return $tree;
    }

    private function configureGeneral(NodeBuilder $node)
    {
        $node
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
            ->end();

        return $node;
    }
}
