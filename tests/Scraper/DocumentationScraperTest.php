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
use RouterOS\Generator\Contracts\CacheInterface;
use RouterOS\Generator\Contracts\SubMenuManagerInterface;
use RouterOS\Generator\Model\SubMenu;
use RouterOS\Generator\Scraper\Configuration;
use RouterOS\Generator\Scraper\DocumentationScraper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

class DocumentationScraperTest extends KernelTestCase
{
    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var MockObject|CacheInterface
     */
    private $cache;

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
        $this->cache = $this->createMock(CacheInterface::class);
        $this->manager = $this->createMock(SubMenuManagerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

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
        $cache = $this->cache;
        $contents = file_get_contents(__DIR__.'/../Fixtures/page/interface.html');

        $config = Yaml::parseFile(__DIR__.'/../Fixtures/scraper/routeros/interface.yml');
        $config = [
            'pages' => [
                $config['name'] => $config,
            ],
        ];
        $cache->expects($this->once())
            ->method('processYamlConfig')
            ->willReturn($config);
        $cache->expects($this->once())
            ->method('getHtmlPage')
            ->with('https://wiki.mikrotik.com/wiki/Manual:Interface')
            ->willReturn($contents);

        $manager->expects($this->once())
            ->method('findOrCreate')
            ->willReturn($subMenu);

        $manager->expects($this->once())
            ->method('update')
            ->with($subMenu);

        $scraper->start();

        $this->assertTrue($subMenu->hasProperty('disabled'));
        $this->assertTrue($subMenu->hasProperty('comment'));
    }
}
