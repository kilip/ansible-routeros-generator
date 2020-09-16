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

use PHPUnit\Framework\MockObject\MockObject;
use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Structure\ResourceManager;
use RouterOS\Generator\Structure\ResourceStructure;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ResourceManagerTest extends KernelTestCase
{
    /**
     * @var MockObject|CacheManagerInterface
     */
    private $cacheManager;

    /**
     * @var string
     */
    private $resourceCompiledDir;

    /**
     * @var ResourceManager
     */
    private $manager;

    protected function setUp(): void
    {
        $this->cacheManager = $this->createMock(CacheManagerInterface::class);
        $this->resourceCompiledDir = __DIR__.'/../Fixtures/cache/compiled';

        $this->manager = new ResourceManager(
            $this->cacheManager,
            $this->resourceCompiledDir
        );
    }

    public function testGetResource()
    {
        $cacheManager = $this->cacheManager;
        $manager = $this->manager;
        $resource = $this->createMock(ResourceStructure::class);

        $index = [
            'test' => [
                'name' => 'test',
                'config_file' => 'test.yaml',
            ],
        ];

        $cacheManager
            ->expects($this->once())
            ->method('parseYaml')
            ->with($this->resourceCompiledDir.'/index.yaml')
            ->willReturn($index);

        $cacheManager
            ->expects($this->once())
            ->method('getYamlObject')
            ->with(ResourceStructure::class, 'test.yaml')
            ->willReturn($resource);

        $this->assertSame(
            $resource,
            $manager->getResource('test')
        );
    }
}
