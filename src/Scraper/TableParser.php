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

namespace RouterOS\Scraper;

use League\HTMLToMarkdown\HtmlConverter;
use RouterOS\Model\SubMenu;
use Symfony\Component\DomCrawler\Crawler;

class TableParser
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $tableIndex;

    private $rows = [];

    private $page;

    public function __construct(array $config)
    {
        $this->url = $config['url'];
        $index = $config['table_index'];
        if (!\is_array($index)) {
            $index = [$index];
        }
        $this->tableIndex = $index;
    }

    /**
     * @param SubMenu $resource
     *
     * @return TableParser
     */
    public static function fromSubMenu(SubMenu $resource)
    {
        $config = $resource->getGenerator();

        return new self($config);
    }

    public function parse($page)
    {
        $url = $this->url;

        $crawler = new Crawler($page, $url);
        $crawler
            ->filter('table')
            ->each(\Closure::fromCallable([$this, 'parseTable']));

        return $this->rows;
    }

    public function parseTable(Crawler $crawler, $index)
    {
        if (
            !\in_array($index, $this->tableIndex, true)
        ) {
            return;
        }

        $crawler
            ->filter('tr')
            ->each(\Closure::fromCallable([$this, 'parseRow']));
    }

    public function parseRow(Crawler $crawler, $index)
    {
        // ignore table header
        if (0 == $index) {
            return;
        }
        $columns = [];

        $crawler->filter('td')->each(function ($crawler, $index) use (&$columns) {
            $html = $crawler->html();
            $converter = new HtmlConverter();
            $markdown = $converter->convert($html);
            $columns[$index] = $markdown;
        });

        $this->rows[] = $columns;
    }
}
