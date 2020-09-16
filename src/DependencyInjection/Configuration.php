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

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('routeros');

        $root = $builder->getRootNode()->children();

        $this->configureGeneralConfig($root);
        $root->end();

        return $builder;
    }

    private function configureGeneralConfig(NodeBuilder $root)
    {
        $root
            ->scalarNode('cache_dir')->isRequired()->end()
            ->scalarNode('config_dir')->isRequired()->end()
            ->scalarNode('compiled_dir')->isRequired()->end()
            ->arrayNode('ansible')
                ->isRequired()
                ->children()
                    ->scalarNode('target_dir')->isRequired()->end()
                    ->scalarNode('module_name_prefix')->isRequired()->end()
                ->end()
            ->end();
    }
}
