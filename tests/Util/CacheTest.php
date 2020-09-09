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
use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Scraper\Configuration;
use RouterOS\Generator\Util\CacheManager;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CacheTest extends TestCase
{
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

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->adapter = $this->createMock(CacheAdapter::class);
        $this->cacheItem = $this->createMock(ItemInterface::class);

        $this->cacheDir = __DIR__.'/../Fixtures/cache';
        $this->cache = new CacheManager(
            $this->dispatcher,
            $this->adapter,
            $this->httpClient,
            $this->cacheDir
        );
        $this->clearCache();
    }

    public function testParseYaml()
    {
        $cache = $this->cache;
        $file = __DIR__.'/../Fixtures/scraper/routeros/interface.yml';
        $result = $cache->parseYaml($file);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('interface', $result['name']);
    }

    public function testProcessYamlConfig()
    {
        $cache = $this->cache;
        $path = __DIR__.'/../Fixtures/scraper/routeros';
        $configuration = new Configuration();

        $result = $cache->processYamlConfig(
            $configuration,
            'routeros.pages',
            $path
        );

        $this->assertIsArray($result);
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

    private function clearCache()
    {
        $finder = Finder::create()
            ->in($this->cacheDir)
            ->name('*.php')
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
