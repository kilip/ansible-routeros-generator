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

namespace Tests\RouterOS\Generator\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\RouterOS\Generator\Concerns\InteractsWithCommand;

class MetaCommandTest extends KernelTestCase
{
    use InteractsWithCommand;

    public function testExecute()
    {
        $tester = $this->getCommandTester('routeros:meta:reindex');
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        $this->assertMatchesRegularExpression('#Reindex Meta Started#', $output);
        $this->assertMatchesRegularExpression('#1/1#', $output);
        $this->assertMatchesRegularExpression('#Processing interface#', $output);
    }
}
