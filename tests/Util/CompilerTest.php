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

use RouterOS\Generator\Util\Compiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\RouterOS\Generator\Concerns\InteractsWithContainer;
use Tests\RouterOS\Generator\Concerns\InteractsWithFilesystem;

class CompilerTest extends KernelTestCase
{
    use InteractsWithContainer;
    use InteractsWithFilesystem;

    private $targetFile;

    protected function setUp(): void
    {
        $this->targetFile = sys_get_temp_dir().'/routeros-generator/templates/output';
        $this->removeDir(\dirname($this->targetFile));
    }

    public function testCompile()
    {
        $twig = $this->getContainer()->get('twig');
        $kernelProjectDir = $this->getContainer()->getParameter('kernel.project_dir');
        $compiler = new Compiler($twig, $kernelProjectDir);
        $template = '@tests/test.py.twig';
        $target = $this->targetFile;

        $compiler->compile(
            $template,
            $target,
            [
                'foo' => 'bar',
            ]
        );

        $contents = file_get_contents($target);
        $this->assertFileExists($target);
        $this->assertRegExp('/foo=bar/', $contents);
    }
}
