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

use Symfony\Component\Filesystem\Filesystem as SymfonyFileSystem;

/**
 * Class Filesystem.
 */
class Filesystem
{
    /**
     * @param mixed $filesOrDirectory
     */
    public function remove($filesOrDirectory)
    {
        $fs = new SymfonyFilesystem();
        $fs->remove($filesOrDirectory);
    }

    public function mirror($source, $target)
    {
        $fs = new SymfonyFileSystem();
        $fs->mirror($source, $target);
    }

    public function ensureFileExists(string $file)
    {
        $this->ensureDirExists(\dirname($file));
        if (!is_file($file)) {
            touch($file);
            chmod($file, 0775);
        }

        return $this;
    }

    public function ensureDirExists(string $dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $this;
    }
}
