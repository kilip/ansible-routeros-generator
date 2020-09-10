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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class RouterosExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', []);
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');
        $loader->load('ansible.xml');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('routeros.scraper.config_dir', $config['scraper_config_dir']);
        $container->setParameter('routeros.cache_dir', $config['cache_dir']);
        $container->setParameter('routeros.scraper.page_cache_lifetime', 604800);

        $this->configureExtensions($container, 'ansible', $config['ansible']);
    }

    private function configureExtensions(ContainerBuilder $container, $root, $config)
    {
        foreach ($config as $key => $value) {
            $name = "{$root}.{$key}";
            $container->setParameter($name, $value);
        }
    }
}
