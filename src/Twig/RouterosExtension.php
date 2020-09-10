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
        ];
    }

    public function yamlDump(array $data, int $indent = 0)
    {
        $spaces = str_repeat('  ', $indent);

        $output = Yaml::dump($data, 10, 2);
        $output = Text::fixYamlDump($output);

        $contents = [];
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            $contents[] = $spaces.$line;
        }

        return implode("\n", $contents);
    }
}
