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
use RouterOS\Generator\Contracts\ResourceManagerInterface;
use RouterOS\Generator\Structure\ResourceProperty;
use RouterOS\Generator\Structure\ResourceStructure;
use RouterOS\Generator\Util\Text;
use Symfony\Component\Yaml\Yaml;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RouterosExtension extends AbstractExtension
{
    /**
     * @var ResourceManagerInterface
     */
    private $resourceManager;

    public function __construct(ResourceManagerInterface $resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('routeros_yaml_dump', [$this, 'yamlDump']),
            new TwigFilter('routeros_prefix', [$this, 'prefix']),
            new TwigFilter('spacing', [$this, 'spacing']),
            new TwigFilter('classify', [$this, 'classify']),
            new TwigFilter('to_json', [$this, 'toJson']),
            new TwigFilter('fix_json', [$this, 'fixJson']),
            new TwigFilter('to_routeros_export', [$this, 'toRouterOSExport']),
            new TwigFilter('quote_value', [$this, 'quoteValue']),
            new TwigFilter('convert', [$this, 'convert']),
            new TwigFilter('to_bool', [$this, 'toBool']),
        ];
    }

    public function toBool($value)
    {
        if ('yes' == $value) {
            return 'True';
        }
        if ('no' == $value) {
            return 'False';
        }

        return $value;
    }

    public function convert($value, $name, $propertyName, $andQuote = false)
    {
        $resourceManager = $this->resourceManager;
        $resource = $resourceManager->getResource($name);
        $property = $resource->getProperty($propertyName);

        $type = $property->getType();
        $elements = $property->getElements();

        if (0 === $value) {
            return 0;
        }

        if (\in_array($value, ['on', 'off'], true)) {
            return '"'.$value.'"';
        }
        if (\in_array($value, ['yes', 'no'], true)) {
            return 'yes' === $value ? 'True' : 'False';
        }

        if (
            ResourceProperty::TYPE_INTEGER == $type
            || ResourceProperty:: TYPE_INTEGER == $elements
        ) {
            $value = (int) $value;
        } elseif (
            ResourceProperty::TYPE_STRING == $type
            || ResourceProperty::TYPE_STRING == $elements
        ) {
            if ($andQuote) {
                $value = '"'.$value.'"';
            }
        }

        return $value;
    }

    public function quoteValue($value)
    {
        if (!is_numeric($value)) {
            $value = '"'.$value.'"';
        }

        return $value;
    }

    public function spacing($contents, int $indent = 0)
    {
        $prefix = str_repeat('  ', $indent);

        $lines = explode("\n", $contents);
        $contents = [];

        foreach ($lines as $line) {
            $contents[] = $prefix.$line;
        }

        return implode("\n", $contents);
    }

    public function toRouterOSExport(array $values, ResourceStructure $resource)
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
            if ('' !== trim($line)) {
                $contents[] = $spaces.rtrim($line);
            }
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
