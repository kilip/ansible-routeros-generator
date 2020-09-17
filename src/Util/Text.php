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

use Doctrine\Inflector\InflectorFactory;
use RouterOS\Generator\Structure\ResourceStructure;

class Text
{
    public static function toRouterosExport(ResourceStructure $resource, array $values)
    {
        $type = $resource->getType();
        $contents = self::arrayToRouteros($resource, $values);
        if ('config' == $type) {
            $contents = preg_replace("#(remove .*[\n]?)#", '', $contents);
            $contents = preg_replace('#(set) (.*)#', 'add \\2', $contents);
            $contents = preg_replace("#\/system script run .*\\n?#", '', $contents);
        }

        return <<<EOC
# RouterOS Output
#
{$contents}
EOC;
    }

    public static function quoteRouterOSValue($value)
    {
        if (false !== strpos($value, ' ')) {
            $value = '"'.$value.'"';
        }

        return $value;
    }

    public static function toRouterosCommands(ResourceStructure $resource, $values)
    {
        $command = $resource->getCommand();
        $commands = [];

        foreach ($values as $value) {
            $action = $value['action'];
            $cmds = [];
            if ('script' == $action) {
                $cmds[] = $value['script'];
            } else {
                $type = $resource->getType();
                $keys = $resource->getKeys();
                $cmds = [$command, $action];
                $vals = $value['values'];
                if ('config' === $type && \in_array($action, ['set', 'remove'], true)) {
                    $finds = [];
                    foreach ($vals as $name => $val) {
                        if (\in_array($name, $keys, true)) {
                            $val = static::quoteRouterOSValue($val);
                            $originalName = static::getOriginalName($resource, $name);
                            $finds[] = "{$originalName}=$val";
                            unset($vals[$name]);
                        }
                    }
                    $cmds[] = '[ find '.implode(' ', $finds).' ]';
                }
                foreach ($vals as $name => $val) {
                    $originalName = static::getOriginalName($resource, $name);
                    $val = static::quoteRouterOSValue($val);
                    $cmds[] = "{$originalName}={$val}";
                }
            }
            $commands[] = implode(' ', $cmds);
        }

        return $commands;
    }

    public static function arrayToRouteros(ResourceStructure $resource, $values, $asCommandList = false)
    {
        $command = $resource->getCommand();
        $configs = [];
        foreach ($values as $config) {
            $action = $config['action'];
            $cmds = [];
            unset($config['action']);

            if ('script' == $action) {
                $cmds[] = $config['script'];
            } else {
                $cmds = [$action];
                ksort($config['values']);
                foreach ($config['values'] as $name => $value) {
                    if (empty($value)) {
                        continue;
                    }
                    if (false !== strpos($value, ' ')) {
                        $value = '"'.$value.'"';
                    }
                    $originalName = static::getOriginalName($resource, $name);
                    $cmds[] = $originalName.'='.$value;
                }
            }
            $configs[] = implode(' ', $cmds);
        }

        if ($asCommandList) {
            foreach ($configs as $index => $config) {
                $configs[$index] = $command.' '.$config;
            }

            return $configs;
        }
        $contents = "{$command}\n".implode("\n", $configs);

        return $contents;
    }

    public static function getOriginalName(ResourceStructure $resource, $name)
    {
        return $resource->getProperty($name, true)->getOriginalName();
    }

    public static function fixYamlDump($dump)
    {
        $dump = preg_replace("#-(\s+)#", '- ', $dump);

        // strip empty values
        return preg_replace("#(.*[\{|\[]\s+?[\}|\]]\n)#", '', $dump);
    }

    public static function normalizeName($name)
    {
        return strtr($name, [
            '/' => '',
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

    public static function arrayKeySort($orders, array $array)
    {
        uksort($array, function ($a, $b) use ($orders) {
            $ixA = array_search($a, $orders, true);
            $ixB = array_search($b, $orders, true);
            if ($ixA == $ixB) {
                return 0;
            }
            if ($ixA > $ixB) {
                return -1;
            }

            return 1;
        });

        return $array;
    }

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
                $name = self::normalizeName($matches[1][$i]);
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

    public static function stripPythonBool($contents)
    {
        return strtr($contents, [
            "'False'" => 'False',
            "'True'" => 'True',
            "''" => "'",
            '"False"' => 'False',
            '"True"' => 'True',
        ]);
    }

    public static function stripQuotes(string $output)
    {
        return preg_replace("#(\')(.*)(\')#", '\\2', $output);
    }

    public static function classify(string $word)
    {
        return static::getInflector()->classify($word);
    }

    public static function getInflector()
    {
        return InflectorFactory::create()->build();
    }
}
