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

trait ResourcePropertiesConfigurationTrait
{
    public function configurePropertiesNode(NodeBuilder $node, $nodeName)
    {
        $node
            ->arrayNode($nodeName)
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('name')->end()
                        ->scalarNode('original_name')->end()
                        ->scalarNode('type')->end()
                        ->scalarNode('elements')->end()
                        ->booleanNode('required')->end()
                        ->scalarNode('default')
                            ->beforeNormalization()
                                ->ifArray()
                                ->then(function ($v) {
                                    return serialize($v);
                                })
                            ->end()
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
            ->end();

        return $node;
    }
}
