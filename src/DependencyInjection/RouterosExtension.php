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

        $container->setParameter('routeros.config_dir', $config['config_dir']);
        $this->configureParameters($container, 'routeros', $config);
        $this->configureParameters($container, 'ansible', $config['ansible']);

        $this->configureMeta($container);
        $this->configureAnsible($container);
    }

    private function configureParameters(ContainerBuilder $container, $root, $config)
    {
        foreach ($config as $key => $value) {
            $name = "{$root}.{$key}";
            if (!\is_array($value)) {
                $container->setParameter($name, $value);
            }
        }
    }

    private function configureMeta(ContainerBuilder $container)
    {
        $configDir = $container->getParameter('routeros.config_dir');
        $compiledDir = $container->getParameter('routeros.compiled_dir');

        $container->setParameter(
            'routeros.meta.config_dir',
            "{$configDir}/meta"
        );
        $container->setParameter(
            'routeros.meta.compiled_dir',
            "{$compiledDir}/meta"
        );

        $container->setParameter(
            'routeros.resource.compiled_dir',
            "{$compiledDir}/resource"
        );
    }

    private function configureAnsible(ContainerBuilder $container)
    {
        $configDir = $container->getParameter('routeros.config_dir');
        $compiledDir = $container->getParameter('routeros.compiled_dir');

        $container->setParameter(
            'ansible.compiled_dir',
            "{$compiledDir}/ansible"
        );
        $container->setParameter(
            'ansible.config_dir',
            "{$configDir}/ansible/modules"
        );
    }
}
