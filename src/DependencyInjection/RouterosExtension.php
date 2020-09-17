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

use RouterOS\Generator\Contracts\ProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;

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

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('routeros.config_dir', $config['config_dir']);
        $this->configureParameters($container, 'routeros', $config);

        $this->configureMeta($container);

        $providers = static::loadProviders();
        foreach ($providers as $provider) {
            $configKey = $provider->getConfigKey();
            $this->configureParameters($container, $configKey, $config[$configKey]);
            $provider->load($container, $config[$configKey]);
        }
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

    /**
     * @return ProviderInterface[]
     */
    public static function loadProviders(): array
    {
        $namespace = 'RouterOS\\Generator\\Provider';
        $providersPaths = realpath(__DIR__.'/../Provider');
        $providers = [];

        $finder = Finder::create()
            ->in($providersPaths)
            ->name('*Provider.php');

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $relativePath = $file->getRelativePath();
            $className = $file->getBasename('.php');
            $className = "{$namespace}\\{$relativePath}\\{$className}";
            $providers[] = new $className();
        }

        return $providers;
    }
}
