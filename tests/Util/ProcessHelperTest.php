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

namespace Tests\RouterOS\Generator\Util;

use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Util\ProcessHelper;

class ProcessHelperTest extends TestCase
{
    public function testFindExecutable()
    {
        $process = new ProcessHelper();
        $extraDirs = [
            realpath(__DIR__.'/../../bin'),
        ];
        $return = $process->findExecutable('console', null, $extraDirs);
        $expected = realpath(__DIR__.'/../../bin/console');
        $this->assertEquals($expected, $return);
    }
}
