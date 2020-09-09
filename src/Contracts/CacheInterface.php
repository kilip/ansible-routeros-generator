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

namespace RouterOS\Generator\Contracts;

use Symfony\Component\Config\Definition\ConfigurationInterface;

interface CacheInterface
{
    /**
     * @param ConfigurationInterface $configuration
     * @param string                 $rootName
     * @param string                 $path
     *
     * @return array Processed Configuration
     */
    public function processYamlConfig(ConfigurationInterface $configuration, string $rootName, string $path): array;

    /**
     * Parse YAML.
     *
     * @param string $file
     *
     * @return array
     */
    public function parseYaml(string $file): array;

    /**
     * Get a cached page url.
     *
     * @param string $url
     *
     * @return string
     */
    public function getHtmlPage(string $url): string;
}
