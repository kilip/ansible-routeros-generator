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

namespace Tests\RouterOS\Generator\Structure;

use RouterOS\Generator\Concerns\InteractsWithStructure;
use RouterOS\Generator\Structure\MetaConfiguration;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MetaConfigurationTest extends KernelTestCase
{
    use InteractsWithStructure;

    /**
     * @param string $name
     * @param mixed  $expected
     * @param string $module
     * @dataProvider getTestProcessConfiguration
     */
    public function testProcessConfiguration($module, $name, $expected)
    {
        /** @var \RouterOS\Generator\Contracts\CacheManagerInterface $cache */
        $cache = $this->getService('routeros.util.cache_manager');
        $configDir = $this->getParameter('routeros.meta.config_dir');

        $configuration = new MetaConfiguration();
        $data = $cache->processYamlConfig($configuration, $configDir);

        $this->assertEquals($expected, $data[$module][$name]);
    }

    public function getTestProcessConfiguration()
    {
        return [
            ['bridge', 'package', 'interface.bridge'],
            ['interface', 'package', 'interface'],
        ];
    }
}
