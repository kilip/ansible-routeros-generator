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

namespace RouterOS\Generator\Provider\Ansible;

use RouterOS\Generator\Contracts\ProviderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class AnsibleProvider implements ProviderInterface
{
    public function getConfigKey(): string
    {
        return 'ansible';
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @throws \Exception
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $environment = $container->getParameter('kernel.environment');
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

        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/Resources/config')
        );
        $loader->load('services.xml');
        if ('test' !== $environment) {
            $loader->load('prepare.xml');
            $loader->Load('build.xml');
        }
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->isRequired()
            ->children()
                ->scalarNode('default_author')
                    ->defaultValue('Anthonius Munthi (@kilip)')
                ->end()
                ->scalarNode('git_repository')
                    ->defaultValue('https://github.com/kilip/ansible-collection-routeros.git')
                ->end()
                ->scalarNode('target_dir')->isRequired()->end()
                ->scalarNode('module_prefix')->isRequired()->end()
                ->scalarNode('module_full_prefix')->isRequired()->end()
            ->end();
    }
}
