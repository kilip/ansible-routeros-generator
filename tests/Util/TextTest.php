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
    /**
     * @param string $text
     * @param string $pattern
     * @dataProvider getNormalizeTextData
     */
    public function testNormalizeText($text, $pattern)
    {
        $result = Text::normalizeText($text);

        $textFile = __DIR__."/fixtures/text/{$text}";
        $patternFile = __DIR__."/fixtures/text/{$pattern}";
        if (is_file($textFile) && $patternFile) {
            $contents = Text::normalizeText(file_get_contents($textFile));
            $this->assertStringEqualsFile($patternFile, $contents);
        } else {
            //$this->assertMatchesRegularExpression("#".$pattern.'#', $result);
            $this->assertEquals($pattern, $result);
        }
    }

    public function getNormalizeTextData()
    {
        return [
            ['“Some Text”', '"Some Text"'],
            ['“C”', '"C"'],
            ['long1', 'long1_expected'],
            [
                'The dog      has a long   tail, and it     is RED!',
                'The dog has a long tail, and it is RED!',
            ],
            ['L(          connect-list, #Connect_List)', 'L(connect-list, #Connect_List)'],
            ['long2', 'long2_expected'],
            ['long3', 'long3_expected'],
        ];
    }

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
                    'key1' => 'value',
                    'key2' => 'value',
                    'foo' => 'bar',
                    'hello' => 'world',
                ],
            ],
            [
                'action' => 'add',
                'values' => [
                    'key1' => 'value',
                    'key2' => 'value',
                    'hello' => 'world',
                ],
            ],
            [
                'action' => 'remove',
                'values' => [
                    'key1' => 'value',
                    'key2' => 'value',
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
            ->setKeys(['key1', 'key2']);
        $commands = Text::toRouterosCommands($resource, $values);
        $this->assertEquals('/test set [ find key1=value and key2=value ] foo=bar hello=world', $commands[0]);
        $this->assertEquals('/test add key1=value key2=value hello=world', $commands[1]);
        $this->assertEquals('/test remove [ find key1=value and key2=value ]', $commands[2]);
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

    public function testExtractParameters()
    {
        $text = '/interface bridge set [ find name=br-wan ] arp=enabled comment="replaced comment"';
        $command = '/interface bridge';

        $output = Text::extractParameters($command, $text);
        $output = $output[0];
        $this->assertEquals('replaced comment', $output['values']['comment']);
    }
}
