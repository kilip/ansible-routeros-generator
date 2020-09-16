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

namespace Tests\RouterOS\Generator\Provider\Ansible\Twig;

use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Provider\Ansible\Twig\AnsibleExtension;

class AnsibleExtensionTest extends TestCase
{
    /**
     * @param string $output
     * @param string $expected
     * @dataProvider getNormalizeLinksData
     */
    public function testNormalizeLinks(string $output, string $expected)
    {
        $extension = new AnsibleExtension();
        $this->assertSame($expected, $extension->normalizeOutput($output));
    }

    public function getNormalizeLinksData()
    {
        return [
            [
                '[ IP/ARP](https://wiki.mikrotik.com/wiki/Manual:IP/ARP "Manual:IP/ARP")',
                'L(IP/ARP, https://wiki.mikrotik.com/wiki/Manual:IP/ARP)',
            ],
            [
                '[ IP/ARP](https://wiki.mikrotik.com/wiki/Manual:IP/ARP )',
                'L(IP/ARP, https://wiki.mikrotik.com/wiki/Manual:IP/ARP)',
            ],
            [
                '[IP/ARP](https://wiki.mikrotik.com/wiki/Manual:IP/ARP)',
                'L(IP/ARP, https://wiki.mikrotik.com/wiki/Manual:IP/ARP)',
            ],
            [
                '[ IP/ARP](https://wiki.mikrotik.com/wiki/Manual:IP/ARP "Manual:IP/ARP")',
                'L(IP/ARP, https://wiki.mikrotik.com/wiki/Manual:IP/ARP)',
            ],
            ['<var>foo</var>', 'C(foo)'],
        ];
    }
}
