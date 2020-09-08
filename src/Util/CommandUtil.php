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

class CommandUtil
{
    public static function extractParameters($command, $text)
    {
        $exp = explode($command, $text);
        $info = $exp[1];

        preg_match('(add|set|remove)', $info, $matches);
        $action = $matches[0];
        $exp = explode($action, $info);
        unset($exp[0]);

        $parameters = [];
        foreach ($exp as $item) {
            $regex = '#(\S+)\=(\".*\"|\S+)#';
            preg_match_all($regex, $item, $matches);
            $param = [];
            for ($i = 0; $i < \count($matches[0]); ++$i) {
                $name = Text::normalizeName($matches[1][$i]);
                $value = $matches[2][$i];
                $param[$name] = $value;
            }
            $parameters[] = [
                'action' => $action,
                'values' => $param,
            ];
        }

        return $parameters;
    }
}
