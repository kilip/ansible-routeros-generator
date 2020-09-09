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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Contracts\SubMenuManagerInterface;
use RouterOS\Generator\Model\SubMenu;
use RouterOS\Generator\Scraper\Configuration;
use RouterOS\Generator\Scraper\DocumentationScraper;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Cache\ItemInterface as CacheItem;

class DocumentationScraperTest extends TestCase
{
    private $output;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var MockObject|CacheAdapter
     */
    private $cache;

    /**
     * @var MockObject|CacheItem
     */
    private $cacheItem;

    /**
     * @var DocumentationScraper
     */
    private $scraper;

    /**
     * @var MockObject|SubMenuManagerInterface
     */
    private $manager;

    protected function setUp(): void
    {
        $contents = file_get_contents(__DIR__.'/../Fixtures/page/interface.html');

        $this->output = $this->createMock(OutputInterface::class);
        $this->cache = $this->createMock(CacheAdapter::class);
        $this->cacheItem = $this->createMock(CacheItem::class);
        $this->manager = $this->createMock(SubMenuManagerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->cache
            ->expects($this->any())
            ->method('getItem')
            ->willReturn($this->cacheItem);

        $this->cacheItem
            ->expects($this->any())
            ->method('isHit')
            ->willReturn(true);

        $this->cacheItem
            ->expects($this->any())
            ->method('get')
            ->willReturn($contents);

        $this->scraper = new DocumentationScraper(
            $this->dispatcher,
            $this->cache,
            new Configuration(),
            $this->manager,
            __DIR__.'/../Fixtures/scraper/routeros'
        );
    }

    public function testStart()
    {
        $scraper = $this->scraper;
        $manager = $this->manager;
        $subMenu = new SubMenu();

        $manager->expects($this->once())
            ->method('findOrCreate')
            ->willReturn($subMenu);

        $manager->expects($this->once())
            ->method('update')
            ->with($subMenu);

        $scraper->start($this->output);

        $this->assertTrue($subMenu->hasProperty('disabled'));
        $this->assertTrue($subMenu->hasProperty('comment'));
    }
}
