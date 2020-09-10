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

namespace Tests\RouterOS\Generator\Concerns;

use Symfony\Component\Filesystem\Filesystem;

trait InteractsWithFilesystem
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    protected function removeDir($dir)
    {
        $fs = $this->getFilesystem();

        $fs->remove($dir);
    }

    private function getFilesystem()
    {
        if (!\is_object($this->filesystem)) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }
}
