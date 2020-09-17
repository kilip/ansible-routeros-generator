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
use RouterOS\Generator\Concerns\InteractsWithContainer;
use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Structure\Meta;
use RouterOS\Generator\Structure\MetaManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MetaManagerTest extends KernelTestCase
{
    use InteractsWithContainer;

    /**
     * @var MockObject|CacheManagerInterface
     */
    private $cacheManager;

    /**
     * @var string
     */
    private $metaCompiledDir;

    /**
     * @var string
     */
    private $manager;

    protected function setUp(): void
    {
        $this->cacheManager = $this->createMock(CacheManagerInterface::class);
        $this->metaCompiledDir = __DIR__.'/../Fixtures/etc/compiled/meta';

        $this->manager = new MetaManager(
            $this->cacheManager,
            $this->metaCompiledDir
        );
    }

    public function testList()
    {
        $cacheManager = $this->cacheManager;
        $manager = $this->manager;
        $config = [
            'interface' => [
                'name' => 'interface',
                'config_file' => $this->metaCompiledDir.'/interface/interface.yaml',
            ],
        ];

        $meta = new Meta();

        $cacheManager
            ->expects($this->once())
            ->method('parseYaml')
            ->with($this->metaCompiledDir.'/index.yaml')
            ->willReturn($config);

        $cacheManager
            ->expects($this->once())
            ->method('getYamlObject')
            ->with(Meta::class, $config['interface']['config_file'])
            ->willReturn($meta);

        $this->assertSame($meta, $manager->getMeta('interface'));
    }
}
