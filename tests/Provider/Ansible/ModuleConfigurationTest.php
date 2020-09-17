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

use RouterOS\Generator\Concerns\InteractsWithContainer;
use RouterOS\Generator\Provider\Ansible\ModuleConfiguration;
use RouterOS\Generator\Util\CacheManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ModuleConfigurationTest extends KernelTestCase
{
    use InteractsWithContainer;

    public function testProcessConfiguration()
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $adapter = $this->createMock(AdapterInterface::class);
        $httpClient = $this->createMock(HttpClientInterface::class);

        $configuration = new ModuleConfiguration();
        $loader = new CacheManager(
            $dispatcher,
            $adapter,
            $httpClient,
            $this->getParameter('routeros.cache_dir'),
            $this->getParameter('kernel.project_dir')
        );

        $modules = $loader->processYamlConfig(
            $configuration,
            'modules',
            __DIR__.'/../../Fixtures/etc/ansible/modules'
        );

        $this->assertArrayHasKey('bridge', $modules);
        $this->assertArrayHasKey('bridge_settings', $modules);
    }
}
