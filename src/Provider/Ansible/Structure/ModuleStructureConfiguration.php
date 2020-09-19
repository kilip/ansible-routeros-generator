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

namespace RouterOS\Generator\Provider\Ansible\Structure;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ModuleStructureConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('modules');

        $rootNode = $builder->getRootNode();

        $rootNode
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('name')->end()
                    ->scalarNode('package')->end()
                    ->scalarNode('version_added')->end()
                    ->scalarNode('author')->isRequired()->end()
                    ->scalarNode('module_name')
                        ->isRequired()
                    ->end()
                    ->scalarNode('short_description')->isRequired()->end()
                    ->scalarNode('type')->defaultValue('config')->end()
                    ->scalarNode('config_file')->end()
                    ->scalarNode('module_template')
                        ->defaultValue('@ansible/module/module.py.twig')
                    ->end()
                    ->arrayNode('description')
                        ->isRequired()
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('supports')
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('ignores')
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('states')
                        ->defaultValue(['merged', 'replaced', 'overridden', 'deleted'])
                        ->scalarPrototype()->end()
                    ->end()
                    ->scalarNode('default_state')->end()
                    ->arrayNode('keys')
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('fixtures')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('action')->isRequired()->end()
                                ->arrayNode('values')
                                    ->requiresAtLeastOneElement()
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('integration')
                        ->variablePrototype()->end()
                    ->end()
                    ->arrayNode('tests')
                        ->arrayPrototype()->end()
                    ->end()
                    ->append($this->getExamplesNode())
                ->end()
            ->end();

        return $builder;
    }

    private function getExamplesNode()
    {
        $node = new ArrayNodeDefinition('examples');
        $node
            ->arrayPrototype()
                ->children()
                    ->scalarNode('name')->isRequired()->end()
                    ->scalarNode('title')->isRequired()->end()
                    ->scalarNode('test_idempotency')->end()
                    ->arrayNode('argument_spec')
                        ->isRequired()
                        ->children()
                            ->scalarNode('state')->isRequired()->end()
                            ->arrayNode('config')
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('verify')
                        ->requiresAtLeastOneElement()
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('action')->isRequired()->end()
                                ->arrayNode('values')
                                    ->scalarPrototype()->end()
                                ->end()
                                ->scalarNode('script')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
