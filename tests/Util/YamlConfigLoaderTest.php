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

namespace RouterOS\Tests\Util;

use PHPUnit\Framework\TestCase;
use RouterOS\Scraper\Configuration;
use RouterOS\Util\YamlConfigLoader;

class YamlConfigLoaderTest extends TestCase
{
    public function testProcess()
    {
        $loader = new YamlConfigLoader();
        $configuration = new Configuration();
        $data = $loader->process($configuration, 'routeros.pages', __DIR__.'/../Fixtures/scraper/routeros');
        $this->assertArrayHasKey('pages', $data);
        $this->assertArrayHasKey('interface', $data['pages']);
    }
}
