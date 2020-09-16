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

namespace RouterOS\Generator\Structure;

use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Contracts\ResourceManagerInterface;

class ResourceManager implements ResourceManagerInterface
{
    /**
     * @var CacheManagerInterface
     */
    private $cacheManager;

    /**
     * @var string
     */
    private $resourceCompiledDir;

    /**
     * @var array
     */
    private $list = [];

    public function __construct(
        CacheManagerInterface $cacheManager,
        string $resourceCompiledDir
    ) {
        $this->cacheManager = $cacheManager;
        $this->resourceCompiledDir = $resourceCompiledDir;
    }

    public function getResource(string $name): ResourceStructure
    {
        $list = $this->getList();
        $config = $list[$name] ?? null;

        if (null === $config) {
            throw new \InvalidArgumentException("Resource {$name} not exists. Please run routeros:scrap command");
        }

        $cacheManager = $this->cacheManager;
        $configFile = $config['config_file'];

        return $cacheManager->getYamlObject(ResourceStructure::class, $configFile);
    }

    public function getList(): array
    {
        if (empty($this->list)) {
            $cacheManager = $this->cacheManager;
            $resourceCompiledDir = $this->resourceCompiledDir;

            $this->list = $cacheManager->parseYaml($resourceCompiledDir.'/index.yaml');
        }

        return $this->list;
    }
}
