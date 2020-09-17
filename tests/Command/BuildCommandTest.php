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

use RouterOS\Generator\Concerns\InteractsWithCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BuildCommandTest extends KernelTestCase
{
    use InteractsWithCommand;

    public function testExecute()
    {
        $tester = $this->getCommandTester('build');

        $tester->execute([]);

        $display = $tester->getDisplay(true);

        $this->assertStringContainsString('Building RouterOS', $display);
    }
}