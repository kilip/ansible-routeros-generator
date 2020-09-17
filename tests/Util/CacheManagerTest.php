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

use PHPUnit\Framework\MockObject\MockObject;
use RouterOS\Generator\Concerns\InteractsWithContainer;
use RouterOS\Generator\Structure\Meta;
use RouterOS\Generator\Structure\MetaConfiguration;
use RouterOS\Generator\Util\CacheManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CacheManagerTest extends KernelTestCase
{
    use InteractsWithContainer;

    /**
     * @var CacheManager
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var CacheAdapter
     */
    private $adapter;

    /**
     * @var MockObject|ItemInterface
     */
    private $cacheItem;

    /**
     * @var MockObject|HttpClientInterface
     */
    private $httpClient;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    private $projectDir;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->adapter = $this->createMock(CacheAdapter::class);
        $this->cacheItem = $this->createMock(ItemInterface::class);

        $this->cacheDir = $this->getContainer()->getParameter('routeros.cache_dir');
        $this->projectDir = $this->getContainer()->getParameter('kernel.project_dir');
        $this->cache = new CacheManager(
            $this->dispatcher,
            $this->adapter,
            $this->httpClient,
            $this->cacheDir,
            $this->projectDir
        );
        $this->clearCache();
    }

    public function testParseYaml()
    {
        $cache = $this->cache;
        $file = __DIR__.'/../Fixtures/etc/meta/interface.yaml';
        $result = $cache->parseYaml($file);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('interface', $result['name']);
    }

    public function testProcessYamlConfig()
    {
        $cache = $this->cache;
        $path = __DIR__.'/../Fixtures/etc/meta';
        $configuration = new MetaConfiguration();
        $this->configureAdapter();

        $result = $cache->processYamlConfig(
            $configuration,
            'metas',
            $path
        );

        $this->assertNotEmpty($result);
    }

    public function testGetHtmlPage()
    {
        $client = $this->httpClient;
        $response = $this->createMock(ResponseInterface::class);
        $contents = 'content';
        $cacheItem = $this->cacheItem;
        $cache = $this->cache;

        $this->configureAdapter();

        $client
            ->expects($this->once())
            ->method('request')
            ->with(Request::METHOD_GET, 'some-url')
            ->willReturn($response);
        $response
            ->expects($this->once())
            ->method('getContent')
            ->willReturn($contents);
        $cacheItem->expects($this->once())
            ->method('set')
            ->with($contents);

        $cacheItem->expects($this->once())
            ->method('get')
            ->willReturn($contents);

        $this->assertSame($contents, $cache->getHtmlPage('some-url'));
    }

    public function testGetYamlObject()
    {
        $file = __DIR__.'/../Fixtures/etc/meta/interface.yaml';
        $cache = $this->cache;
        $meta = new Meta();
        $meta->setName('test');
        $object = $cache->getYamlObject(Meta::class, $file);

        $this->assertEquals('interface', $object->getName());
        $this->assertEquals('interface', $object->getPackage());
    }

    public function testMockHttpClient()
    {
        $container = $this->getContainer();
        $cacheManager = $container->get('routeros.util.cache_manager');

        $result = $cacheManager->getHtmlPage('https://wiki.mikrotik.com/wiki/Manual:Interface');
        $expected = file_get_contents(__DIR__.'/../Fixtures/pages/interface.html');

        $this->assertSame($expected, $result);
    }

    private function clearCache()
    {
        $finder = Finder::create()
            ->in($this->cacheDir)
            ->name('*.php')
            ->name('*.dat')
            ->name('*.meta');
        $fs = new Filesystem();

        $fs->remove($finder);
    }

    private function configureAdapter($isHit = false)
    {
        $this->adapter
            ->expects($this->any())
            ->method('getItem')
            ->willReturn($this->cacheItem);
        $this->cacheItem
            ->expects($this->any())
            ->method('isHit')
            ->willReturn($isHit);
    }
}
