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

namespace RouterOS\Generator\Util;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process as Process;

class ProcessHelper
{
    /**
     * @var Process
     */
    private $process;

    public function create(
        array $command,
        string $cwd = null,
        array $env = null,
        $input = null,
        ?float $timeout = 60
    ) {
        $this->process = new Process($command, $cwd, $input, $timeout);

        return $this;
    }

    public function run(array $env = [])
    {
        $callback = function ($type, $buffer) {
            echo $buffer;
        };

        return $this->process->run($callback, $env);
    }

    public function findExecutable(string $name, string $default = null, array $extraDirs = [])
    {
        return (new ExecutableFinder())->find($name, $default, $extraDirs);
    }
}
