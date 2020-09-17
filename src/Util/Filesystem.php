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

class Filesystem
{
    private $args;

    public function __construct($args)
    {
        $this->args = $args;
    }

    public function ensureDir()
    {
        $dir = $this->args[0];
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}
