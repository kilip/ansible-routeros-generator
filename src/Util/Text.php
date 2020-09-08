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

namespace RouterOS\Util;

class Text
{
    public static function fixYamlDump($dump)
    {
        return preg_replace("#-(\s+)#", '- ', $dump);
    }

    public static function normalizeName($name)
    {
        return strtr($name, [
            '/' => '_',
            '-' => '_',
            '.' => '_',
        ]);
    }

    public static function fixMultiQuotes($value)
    {
        return strtr($value, [
            '\'"' => '\'',
            '"\'' => '\'',
        ]);
    }
}
