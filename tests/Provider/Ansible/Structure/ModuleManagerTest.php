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

namespace Tests\RouterOS\Generator\Provider\Ansible\Structure;

use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Provider\Ansible\Structure\ModuleManager;
use Symfony\Component\Yaml\Yaml;

class ModuleManagerTest extends TestCase
{
    private $cacheManager;
    private $ansibleCompiledDir;
    private $manager;

    protected function setUp(): void
    {
        $this->cacheManager = $this->createMock(CacheManagerInterface::class);
        $this->ansibleCompiledDir = __DIR__.'/../../../Fixtures/config/compiled/ansible';

        $this->manager = new ModuleManager(
            $this->cacheManager,
            $this->ansibleCompiledDir
        );
    }

    public function testGetConfig()
    {
        $cacheManager = $this->cacheManager;
        $compiledDir = $this->ansibleCompiledDir;
        $manager = $this->manager;

        $expected = Yaml::parseFile($compiledDir.'/interface/interface.yaml');
        $cacheManager
            ->expects($this->exactly(2))
            ->method('parseYaml')
            ->withConsecutive(
                [$compiledDir.'/index.yaml']
            )
            ->willReturnOnConsecutiveCalls(
                Yaml::parseFile($compiledDir.'/index.yaml'),
                $expected
            );

        $config = $manager->getConfig('interface');

        $this->assertSame($expected, $config);
    }

    public function testGetConfigThrows()
    {
        $cacheManager = $this->cacheManager;
        $compiledDir = $this->ansibleCompiledDir;
        $manager = $this->manager;
        $cacheManager
            ->expects($this->once())
            ->method('parseYaml')
            ->with(
                $compiledDir.'/index.yaml'
            )
            ->willReturn(
                Yaml::parseFile($compiledDir.'/index.yaml')
            );

        $this->expectException(\InvalidArgumentException::class);
        $manager->getConfig('foo');
    }
}
