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

namespace Tests\RouterOS\Generator\Twig;

use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Twig\RouterosExtension;
use Symfony\Component\Yaml\Yaml;

class RouterosExtensionTest extends TestCase
{
    /**
     * @param string $pattern
     * @param string $message
     * @param int    $indent
     * @dataProvider getTestYamlDumpData
     */
    public function testYamlDump($pattern, $message = '', $indent = 1)
    {
        $filter = new RouterosExtension();
        $data = Yaml::parseFile(__DIR__.'/../Fixtures/scraper/routeros/bridge.yml');
        $output = $filter->yamlDump($data, $indent);

        $pattern = "#{$pattern}#";

        if ('' == $message) {
            $message = "pattern not match: {$pattern}";
        }
        $this->assertRegExp($pattern, $output, $message);
    }

    public function getTestYamlDumpData()
    {
        return [
            ['name: bridge'],
            ['^\s{2}name: bridge', 'should prefixed by 2 space', 1],
            ['^\s{4}name: bridge', 'should prefixed by 4 space', 2],
        ];
    }
}
