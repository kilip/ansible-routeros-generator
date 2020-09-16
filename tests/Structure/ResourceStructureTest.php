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
use RouterOS\Generator\Structure\Meta;
use RouterOS\Generator\Structure\ResourceProperty;
use RouterOS\Generator\Structure\ResourceStructure;
use Symfony\Component\Yaml\Yaml;
use Tests\RouterOS\Generator\Concerns\InteractsWithYaml;

class ResourceStructureTest extends TestCase
{
    use InteractsWithYaml;

    public function testGetProperty()
    {
        $resource = new ResourceStructure();
        $this->assertFalse($resource->hasProperty('test'));

        $property = $resource->getProperty('test', true);
        $this->assertIsObject($property);
        $this->assertTrue($resource->hasProperty('test'));

        $this->expectException(\InvalidArgumentException::class);
        $resource->getProperty('failed');
    }

    public function testAddProperty()
    {
        $resource = new ResourceStructure();
        $property = $this->createMock(ResourceProperty::class);

        $property->expects($this->once())
            ->method('getName')
            ->willReturn('test');

        $resource->addProperty($property);
        $this->assertTrue($resource->hasProperty('test'));
        $this->assertSame($property, $resource->getProperty('test'));
    }

    /**
     * @dataProvider getTestFromMetaData
     *
     * @param mixed $name
     * @param mixed $expectedValue
     */
    public function testFromMeta($name, $expectedValue)
    {
        $config = $this->parseYamlFile('yaml/test-meta.yaml');
        $meta = new Meta();
        $meta->fromArray($config);

        $structure = new ResourceStructure();
        $structure->fromMeta($meta);

        $data = $structure->toArray();
        $exp = explode('.', $name);
        if (1 === \count($exp)) {
            $this->assertSame($expectedValue, $data[$name]);

            return;
        }
        $value = $data[$exp[0]][$exp[1]][$exp[2]];
        $this->assertSame($expectedValue, $value);
    }

    public function getTestFromMetaData()
    {
        return [
            ['name', 'bridge'],
            ['properties.disabled.default', 'no'],
            ['properties.mld_version.name', 'mld_version'],
            ['properties.mld_version.default', 1],
        ];
    }

    /**
     * @param string $name
     * @param string $expectedValue
     * @dataProvider getTestFromArray
     */
    public function testFromArray($name, $expectedValue)
    {
        $data = $this->fromYamlFile();
        $resource = new ResourceStructure();
        $resource->fromArray($data);

        $getter = 'get'.$name;
        $this->assertSame($expectedValue, \call_user_func([$resource, $getter]));

        $inflector = InflectorFactory::create()->build();
        $toArray = $resource->toArray();
        $this->assertSame($expectedValue, $toArray[$inflector->tableize($name)]);
    }

    public function getTestFromArray()
    {
        return [
            ['name', 'interface'],
        ];
    }

    private function fromYamlFile()
    {
        return Yaml::parseFile(__DIR__.'/../Fixtures/yaml/interface-resource.yml');
    }
}
