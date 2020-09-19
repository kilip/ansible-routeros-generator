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

namespace RouterOS\Generator\Scraper;

use League\HTMLToMarkdown\HtmlConverter;
use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Structure\Meta;
use Symfony\Component\DomCrawler\Crawler;

class TableParser
{
    /**
     * @var array
     */
    private $currentTableIndex;

    private $rows = [];

    /**
     * @var CacheManagerInterface
     */
    private $cacheManager;

    public function __construct(
        CacheManagerInterface $cacheManager
    ) {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param Meta $meta
     *
     * @return array Lists of table rows
     */
    public function parse(Meta $meta): array
    {
        $cacheManager = $this->cacheManager;
        $config = $meta->getGenerator();
        $url = $config['url'];
        $this->currentTableIndex = $config['table_index'];
        $this->rows = [];

        $page = $cacheManager->getHtmlPage($url);
        $crawler = new Crawler($page, $url);
        $crawler
            ->filter('table')
            ->each(\Closure::fromCallable([$this, 'parseTable']));

        return $this->rows;
    }

    public function parseTable(Crawler $crawler, $index)
    {
        if (
            !\in_array($index, $this->currentTableIndex, true)
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
            $converter = new HtmlConverter([
                'use_autolinks' => true,
                'strip_tags' => true,
                'hard_break' => true,
            ]);
            $markdown = $converter->convert($html);
            $markdown = str_replace('`Read more >>`', 'Read More', $markdown);
            $markdown = strtr($markdown, [
                '[ ' => '[',
                ' ]' => ']',
                '( ' => ')',
                ' )' => ')',
            ]);
            $markdown = str_replace('(/wiki', '(https://wiki.mikrotik.com/wiki', $markdown);
            $columns[$index] = $markdown;
        });

        $this->rows[] = $columns;
    }
}
