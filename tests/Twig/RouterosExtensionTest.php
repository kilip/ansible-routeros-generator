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

use PHPUnit\Framework\MockObject\MockObject;
use RouterOS\Generator\Concerns\InteractsWithContainer;
use RouterOS\Generator\Concerns\InteractsWithStructure;
use RouterOS\Generator\Contracts\ResourceManagerInterface;
use RouterOS\Generator\Twig\RouterosExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

class RouterosExtensionTest extends KernelTestCase
{
    use InteractsWithContainer;
    use InteractsWithStructure;

    /**
     * @var MockObject|ResourceManagerInterface
     */
    private $resourceManager;

    /**
     * @var RouterosExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->resourceManager = $this->createMock(ResourceManagerInterface::class);

        $this->resourceManager
            ->expects($this->any())
            ->method('getResource')
            ->willReturnMap([
                ['interface', $this->createResource('interface.interface')],
                ['bridge'], $this->createResource('interface.bridge.bridge'),
            ]);
        $this->extension = new RouterosExtension(
            $this->resourceManager
        );
    }

    /**
     * @param string $pattern
     * @param string $message
     * @param int    $indent
     * @dataProvider getTestYamlDumpData
     */
    public function testYamlDump($pattern, $message = '', $indent = 1)
    {
        $filter = $this->extension;
        $data = Yaml::parseFile(__DIR__.'/../Fixtures/routeros-extension/bridge.yaml');
        $output = $filter->yamlDump($data, $indent);

        $pattern = "#{$pattern}#";

        if ('' == $message) {
            $message = "pattern not match: {$pattern}";
        }
        $this->assertMatchesRegularExpression($pattern, $output, $message);
    }

    public function getTestYamlDumpData()
    {
        return [
            ['name: bridge'],
            ['^\s{2}name: bridge', 'should prefixed by 2 space', 1],
            ['^\s{4}name: bridge', 'should prefixed by 4 space', 2],
        ];
    }

    /**
     * @param string $name
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expected
     * @param bool   $andQuote
     * @dataProvider getTestConvertData
     */
    public function testConvert($name, $property, $value, $expected, $andQuote = false)
    {
        $extension = $this->extension;
        $result = $extension->convert($value, $name, $property, $andQuote);
        $this->assertSame($expected, $result);
    }

    public function getTestConvertData()
    {
        return [
            ['interface', 'mtu', 1500, '1500'],
            ['interface', 'mtu', 1500, '"1500"', true],
        ];
    }
}
