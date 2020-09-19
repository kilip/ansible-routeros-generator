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

namespace Tests\RouterOS\Generator\Scraper;

use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Concerns\InteractsWithCacheManager;
use RouterOS\Generator\Scraper\PropertyParser;
use RouterOS\Generator\Scraper\TableParser;
use RouterOS\Generator\Structure\Meta;
use RouterOS\Generator\Structure\ResourceStructure;

class PropertyParserTest extends TestCase
{
    use InteractsWithCacheManager;

    protected function setUp(): void
    {
        $this->configureCacheManager();
    }

    /**
     * @param string $propertyName
     * @param string $name
     * @param string $expectedValue
     * @dataProvider getTestParseData
     */
    public function testParse($propertyName, $name, $expectedValue)
    {
        $resource = $this->getParsedResource();

        $this->assertTrue($resource->hasProperty($propertyName));

        $property = $resource->getProperty($propertyName);

        $getter = 'get'.$name;
        $this->assertSame($expectedValue, \call_user_func([$property, $getter]));
    }

    public function getTestParseData()
    {
        return [
            ['l2mtu', 'type', 'integer'],
            ['l2mtu', 'default', null],
            ['mtu', 'type', 'integer'],
            ['name', 'type', 'string'],
        ];
    }

    private function getParsedResource(): ResourceStructure
    {
        /** @var \RouterOS\Generator\Structure\ResourceStructure $resource */
        static $resource;

        if (null === $resource) {
            $meta = $this->createMock(Meta::class);
            $tableParser = new TableParser($this->cacheManager);
            $parser = new PropertyParser();
            $resource = new ResourceStructure();
            $config = [
                'url' => 'https://wiki.mikrotik.com/wiki/Manual:Interface',
                'table_index' => [1],
            ];
            $meta
                ->expects($this->once())
                ->method('getGenerator')
                ->willReturn($config);
            $rows = $tableParser->parse($meta);
            foreach ($rows as $row) {
                $parser->parse($resource, $row[0], $row[1]);
            }
        }

        return $resource;
    }
}
