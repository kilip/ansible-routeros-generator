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

namespace Tests\RouterOS\Generator\Concerns;

use PHPUnit\Framework\MockObject\MockObject;
use RouterOS\Generator\Contracts\CacheManagerInterface;

trait InteractsWithCacheManager
{
    /**
     * @var MockObject|CacheManagerInterface
     */
    protected $cacheManager;

    private $pages;

    public function configureCacheManager(): void
    {
        $pages = [
            [
                'https://wiki.mikrotik.com/wiki/Manual:Interface',
                file_get_contents(__DIR__.'/../Fixtures/pages/interface.html'),
            ],
        ];
        $this->cacheManager = $this->createMock(CacheManagerInterface::class);
        $this->cacheManager
            ->expects($this->any())
            ->method('getHtmlPage')
            ->willReturnMap($pages);
    }

    protected function addHtmlPageFixture(string $url, string $file): void
    {
        $this->pages[] = [$url, file_get_contents($file)];
    }

    protected function getHtmlPage($url)
    {
        foreach ($this->pages as $page) {
            if ($page[0] == $url) {
                return $url;
            }
        }
        throw new \Exception("No fixture for page: {$url}");
    }
}
