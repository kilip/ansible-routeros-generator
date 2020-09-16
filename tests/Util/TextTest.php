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

namespace Tests\RouterOS\Generator\Util;

use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Structure\ResourceStructure;
use RouterOS\Generator\Util\Text;

class TextTest extends TestCase
{
    public function testToRouterosCommandsWithSetting()
    {
        $values = [
            [
                'action' => 'set',
                'values' => [
                    'key' => 'value',
                    'foo' => 'bar',
                    'hello' => 'world',
                ],
            ],
        ];
        $resource = new ResourceStructure();
        $resource
            ->setType('setting')
            ->setCommand('/test');
        $commands = Text::toRouterosCommands($resource, $values);
        $this->assertEquals('/test set key=value foo=bar hello=world', $commands[0]);
    }

    public function testToRouterosCommandsWithConfig()
    {
        $values = [
            [
                'action' => 'set',
                'values' => [
                    'key' => 'value',
                    'foo' => 'bar',
                    'hello' => 'world',
                ],
            ],
            [
                'action' => 'add',
                'values' => [
                    'key' => 'value',
                    'foo' => 'bar',
                    'hello' => 'world',
                ],
            ],
            [
                'action' => 'remove',
                'values' => [
                    'key' => 'value',
                ],
            ],
            [
                'action' => 'script',
                'script' => 'some-script',
            ],
        ];
        $resource = new ResourceStructure();
        $resource
            ->setType('config')
            ->setCommand('/test')
            ->setKeys(['key']);
        $commands = Text::toRouterosCommands($resource, $values);
        $this->assertEquals('/test set [ find key=value ] foo=bar hello=world', $commands[0]);
        $this->assertEquals('/test add key=value foo=bar hello=world', $commands[1]);
        $this->assertEquals('/test remove [ find key=value ]', $commands[2]);
        $this->assertEquals('some-script', $commands[3]);
    }

    public function testArrayToRouteros()
    {
        $resource = new ResourceStructure();
        $resource->setCommand('/interface');
        $values = [
            [
                'action' => 'set',
                'values' => [
                    'foo' => 'bar',
                ],
            ],
            [
                'action' => 'set',
                'values' => [
                    'hello' => 'hello world',
                ],
            ],
        ];

        $expect = <<<EOC
/interface
set foo=bar
set hello="hello world"
EOC;

        $this->assertEquals($expect, Text::arrayToRouteros($resource, $values));
    }
}
