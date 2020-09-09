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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class YamlConfigLoader
{
    public function process(
        ConfigurationInterface $configuration,
        $rootName,
        string $path
    ) {
        $finder = Finder::create()
            ->in($path);

        $configs = [];
        $exp = explode('.', $rootName);

        foreach ($finder->files() as $file) {
            $data = Yaml::parseFile($file->getRealPath());
            $configs[$exp[0]][$exp[1]][] = $data;
        }

        $processor = new Processor();

        return $processor->processConfiguration($configuration, $configs);
    }
}
