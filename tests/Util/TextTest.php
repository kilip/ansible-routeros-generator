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
