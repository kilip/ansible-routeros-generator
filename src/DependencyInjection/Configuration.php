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

namespace RouterOS\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('routeros_scraper');

        $root = $builder->getRootNode()->children();

        $this->configureGeneralConfig($root);
        $root->end();

        return $builder;
    }

    private function configureGeneralConfig(NodeBuilder $root)
    {
        $root
            ->scalarNode('scraper_config_dir')->end();
    }
}
