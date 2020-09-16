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
use RouterOS\Generator\Scraper\PropertyParser;
use RouterOS\Generator\Scraper\Scraper;
use RouterOS\Generator\Scraper\TableParser;
use RouterOS\Generator\Structure\Meta;
use RouterOS\Generator\Structure\ResourceStructure;

class ScraperTest extends TestCase
{
    /**
     * @var MockObject|TableParser
     */
    private $tableParser;

    /**
     * @var MockObject|PropertyParser
     */
    private $propertyParser;

    /**
     * @var Scraper
     */
    private $scraper;

    protected function setUp(): void
    {
        $this->tableParser = $this->createMock(TableParser::class);
        $this->propertyParser = $this->createMock(PropertyParser::class);

        $this->scraper = new Scraper(
            $this->tableParser,
            $this->propertyParser
        );
    }

    public function testScrapPage()
    {
        $scraper = $this->scraper;
        $meta = $this->createMock(Meta::class);
        $tableParser = $this->tableParser;
        $propertyParser = $this->propertyParser;

        $rows = [
            [
                0 => 'info',
                1 => 'description',
            ],
        ];
        $meta->expects($this->once())
            ->method('toArray')
            ->willReturn([]);
        $meta->expects($this->once())
            ->method('getPropertiesOverride')
            ->willReturn([]);

        $tableParser->expects($this->once())
            ->method('parse')
            ->with($meta)
            ->willReturn($rows);

        $propertyParser
            ->expects($this->once())
            ->method('parse')
            ->with(
                $this->isInstanceOf(ResourceStructure::class),
                'info',
                'description'
            );

        $scraper->scrapPage($meta);
    }
}
