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
use Symfony\Component\Console\Command\Command;
use Tests\RouterOS\Generator\Concerns\InteractsWithCommand;

class ScrapCommandTest extends KernelTestCase
{
    use InteractsWithCommand;

    public function testExecute()
    {
        $tester = $this->getCommandTester('routeros:scrap');
        $tester->execute([]);
        $this->assertEquals(Command::SUCCESS, $tester->getStatusCode());
    }
}
