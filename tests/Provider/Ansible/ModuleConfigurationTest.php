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

use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Provider\Ansible\ModuleConfiguration;
use RouterOS\Generator\Util\YamlConfigLoader;
use Symfony\Component\Config\Definition\Processor;

class ModuleConfigurationTest extends TestCase
{
    public function testProcessConfiguration()
    {
        $configuration = new ModuleConfiguration();
        $processor = new Processor();
        $loader = new YamlConfigLoader();

        $config = $loader->process(
            $configuration,
            'ansible.modules',
            __DIR__.'/Fixtures/config'
        );

        $processed = $processor->processConfiguration($configuration, ['modules' => $config]);

        $modules = $processed['modules'];
        $this->assertArrayHasKey('bridge', $modules);
        $this->assertArrayHasKey('bridge_settings', $modules);
    }
}
