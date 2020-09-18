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

interface CacheManagerInterface
{
    /**
     * @return object
     */
    public function getYamlObject(string $className, string $file): object;

    /**
     * @param ConfigurationInterface $configuration
     * @param string                 $path
     * @param bool                   $force
     *
     * @return array Processed Configuration
     */
    public function processYamlConfig(ConfigurationInterface $configuration, string $path, bool $force = false): array;

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
