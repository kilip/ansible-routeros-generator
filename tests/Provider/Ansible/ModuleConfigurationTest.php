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

namespace Tests\RouterOS\Generator\Provider\Ansible;

use RouterOS\Generator\Provider\Ansible\ModuleConfiguration;
use RouterOS\Generator\Util\CacheManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\RouterOS\Generator\Concerns\InteractsWithContainer;

class ModuleConfigurationTest extends KernelTestCase
{
    use InteractsWithContainer;

    public function testProcessConfiguration()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $adapter = $this->createMock(AdapterInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);

        $configuration = new ModuleConfiguration();
        $processor = new Processor();
        $loader = new CacheManager(
            $dispatcher,
            $adapter,
            $httpClient,
            __DIR__.'/Fixtures/compiled'
        );

        $config = $loader->processYamlConfig(
            $configuration,
            'ansible.modules',
            __DIR__.'/Fixtures/modules',
            true
        );

        $processed = $processor->processConfiguration($configuration, ['modules' => $config]);

        $modules = $processed['modules'];
        $this->assertArrayHasKey('bridge', $modules);
        $this->assertArrayHasKey('bridge_settings', $modules);
    }
}
