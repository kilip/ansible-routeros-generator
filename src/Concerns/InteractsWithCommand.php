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

namespace RouterOS\Generator\Concerns;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

trait InteractsWithCommand
{
    use InteractsWithContainer;
    use InteractsWithConsoleOutput;

    /**
     * @param string $command
     *
     * @return CommandTester
     */
    public function getCommandTester(string $command): CommandTester
    {
        $kernel = $this->getKernel();
        $application = new Application($kernel);

        $command = $application->find($command);

        return new CommandTester($command);
    }
}
