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
use RouterOS\Generator\Util\Filesystem;

class FilesystemTest extends TestCase
{
    public function testEnsureDirExists()
    {
        $dir = sys_get_temp_dir().'/routeros-generator/foo';
        @rmdir($dir);
        filesystem()->ensureDirExists($dir);
        $this->assertDirectoryExists($dir);
    }

    public function testEnsureFileExists()
    {
        $file = sys_get_temp_dir().'/routeros/generator/file.php';
        @unlink($file);
        filesystem()->ensureFileExists($file);
        $this->assertFileExists($file);
    }

    public function testMirror()
    {
        $targetDir = sys_get_temp_dir().'/routeros-generator/fs';
        filesystem()->mirror(__DIR__.'/fixtures/fs', $targetDir);

        $this->assertFileExists($targetDir.'/dir1/file1');
    }

    /**
     * @depends testMirror
     */
    public function testCleanupDirectory()
    {
        $targetDir = sys_get_temp_dir().'/routeros-generator/fs';
        filesystem()->cleanupDirectory($targetDir);
        $this->assertDirectoryDoesNotExist($targetDir);
    }
}
