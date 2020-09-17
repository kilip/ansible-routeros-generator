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
use RouterOS\Generator\Scraper\TableParser;
use RouterOS\Generator\Structure\Meta;

class TableParserTest extends TestCase
{
    use InteractsWithCacheManager;

    /**
     * @var TableParser
     */
    private $parser;

    protected function setUp(): void
    {
        $this->configureCacheManager();
        $this->parser = new TableParser($this->cacheManager);
    }

    public function testParse()
    {
        $meta = $this->createMock(Meta::class);
        $parser = $this->parser;

        $config = [
            'url' => 'https://wiki.mikrotik.com/wiki/Manual:Interface',
            'table_index' => [1],
        ];

        $meta
            ->expects($this->once())
            ->method('getGenerator')
            ->willReturn($config);

        $rows = $parser->parse($meta);

        $this->assertCount(3, $rows);
    }
}
