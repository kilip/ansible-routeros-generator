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

namespace RouterOS\Generator\Twig;

use Doctrine\Inflector\InflectorFactory;
use RouterOS\Generator\Structure\ResourceStructure;
use RouterOS\Generator\Util\Text;
use Symfony\Component\Yaml\Yaml;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RouterosExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('routeros_yaml_dump', [$this, 'yamlDump']),
            new TwigFilter('routeros_prefix', [$this, 'prefix']),
            new TwigFilter('classify', [$this, 'classify']),
            new TwigFilter('to_json', [$this, 'toJson']),
            new TwigFilter('fix_json', [$this, 'fixJson']),
            new TwigFilter('to_routeros_export', [$this, 'toRouterOSExport']),
        ];
    }

    public function toRouterOSExport(array $values, ResourceStructure $resource, $spacing = 0)
    {
        return Text::arrayToRouteros($resource, $values);
    }

    public function yamlDump(array $data, int $indent = 0)
    {
        $spaces = str_repeat('  ', $indent);

        $output = Yaml::dump($data, 10, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        $output = Text::fixYamlDump($output);
        $output = Text::stripPythonBool($output);
        $output = Text::stripQuotes($output);

        $contents = [];
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            $contents[] = $spaces.$line;
        }

        return implode("\n", $contents);
    }

    public function prefix($contents, $prefix)
    {
        $lines = explode("\n", $contents);
        $output = [];
        foreach ($lines as $line) {
            $output[] = rtrim($prefix.$line);
        }

        return implode("\n", $output);
    }

    public function classify($input)
    {
        $inflector = InflectorFactory::create()->build();

        return $inflector->classify($input);
    }

    public function fixJson($input, $spacing = 0)
    {
        $prefix = str_repeat('  ', $spacing);
        $input = Text::stripPythonBool($input);

        $lines = explode("\n", $input);

        $contents = [];
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $line = $prefix.$line;
            }
            $contents[] = $line;
        }

        return implode("\n", $contents);
    }

    public function toJson(array $value, $spacing = 0)
    {
        $contents = json_encode($value, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);

        return $this->fixJson($contents, $spacing);
    }
}
