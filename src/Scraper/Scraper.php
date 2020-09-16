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

use RouterOS\Generator\Contracts\ScraperInterface;
use RouterOS\Generator\Structure\Meta;
use RouterOS\Generator\Structure\ResourceStructure;

class Scraper implements ScraperInterface
{
    /**
     * @var TableParser
     */
    private $tableParser;
    /**
     * @var PropertyParser
     */
    private $propertyParser;

    public function __construct(
        TableParser $tableParser,
        PropertyParser $propertyParser
    ) {
        $this->tableParser = $tableParser;
        $this->propertyParser = $propertyParser;
    }

    public function scrapPage(Meta $meta): ResourceStructure
    {
        $resource = new ResourceStructure();
        $resource->fromMeta($meta);

        $tableParser = $this->tableParser;
        $propertyParser = $this->propertyParser;
        try {
            $rows = $tableParser->parse($meta);
            foreach ($rows as $row) {
                try {
                    $propertyParser->parse($resource, $row[0], $row[1]);
                } catch (\Exception $e) {
                    $resource->addException($e);
                }
            }
        } catch (\Exception $e) {
            $resource->addException($e);
        }

        return $resource;
    }
}
