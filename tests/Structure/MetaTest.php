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

use Doctrine\Inflector\InflectorFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class MetaTest extends TestCase
{
    /**
     * @param string $name
     * @param mixed  $expectedValue
     * @dataProvider getTestFromArray
     */
    public function testFromArray($name, $expectedValue)
    {
        $data = $this->fromYamlFile();
        $meta = new \RouterOS\Generator\Structure\Meta();
        $meta->fromArray($data);

        $getter = 'get'.$name;
        $this->assertSame($expectedValue, \call_user_func([$meta, $getter]));

        $inflector = InflectorFactory::create()->build();
        $toArray = $meta->toArray();
        $this->assertSame($expectedValue, $toArray[$inflector->tableize($name)]);
    }

    public function getTestFromArray()
    {
        $generator = [
            'url' => 'https://wiki.mikrotik.com/wiki/Manual:Interface',
            'table_index' => [1],
        ];

        return [
            ['package', 'interface'],
            ['name', 'interface'],
            ['command', '/interface'],
            ['generator', $generator],
            ['type', 'config'],
            ['propertiesOverride', [
                'mtu' => [
                    'type' => 'string',
                ],
                'l2mtu' => [
                    'type' => 'string',
                ],
            ]],
        ];
    }

    private function fromYamlFile()
    {
        return Yaml::parseFile(__DIR__.'/../Fixtures/etc/meta/interface.yaml');
    }
}
