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

namespace Tests\RouterOS\Generator\Provider\Ansible\Structure;

use RouterOS\Generator\Provider\Ansible\Concerns\InteractsWithAnsibleStructure;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ModuleStructureConfigurationTest extends KernelTestCase
{
    use InteractsWithAnsibleStructure;

    /**
     * @dataProvider getProcessConfigurationData
     *
     * @param mixed $expected
     */
    public function testProcessConfiguration(string $module, string $name, $expected)
    {
        /** @var \RouterOS\Generator\Contracts\CacheManagerInterface $cache */
        $cache = $this->getService('routeros.util.cache_manager');
        $configDir = $this->getParameter('ansible.config_dir');

        $configuration = new \RouterOS\Generator\Provider\Ansible\Structure\ModuleStructureConfiguration();
        $data = $cache->processYamlConfig($configuration, $configDir);

        $this->assertEquals($expected, $data[$module][$name]);
    }

    public function getProcessConfigurationData()
    {
        return [
            ['bridge', 'package', 'interface.bridge'],
        ];
    }
}
