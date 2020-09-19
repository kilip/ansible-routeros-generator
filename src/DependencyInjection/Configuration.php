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

namespace RouterOS\Generator\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var TreeBuilder
     */
    private $builder;

    public function getConfigTreeBuilder()
    {
        $this->buildTree();

        return $this->builder;
    }

    public function buildTree()
    {
        $providers = RouterosExtension::loadProviders();
        $builder = new TreeBuilder('routeros');

        $root = $builder->getRootNode();

        $this->getGeneralConfig($root);
        foreach ($providers as $provider) {
            $provider->configure($root->children()->arrayNode($provider->getConfigKey()));
        }

        $root->end();

        $this->builder = $builder;
    }

    private function getGeneralConfig(NodeDefinition $root)
    {
        $root
            ->children()
                ->scalarNode('cache_dir')->isRequired()->end()
                ->scalarNode('config_dir')->isRequired()->end()
                ->scalarNode('compiled_dir')->isRequired()->end()
                ->arrayNode('providers')
                    ->scalarPrototype()->end()
                ->end()
            ->end();
    }
}
