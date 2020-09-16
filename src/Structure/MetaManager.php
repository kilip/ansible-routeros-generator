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
use RouterOS\Generator\Contracts\MetaManagerInterface;

class MetaManager implements MetaManagerInterface
{
    private $cacheManager;

    private $metaCompiledDir;

    /**
     * @var array
     */
    private $list;

    public function __construct(
        CacheManagerInterface $cacheManager,
        string $metaCompiledDir
    ) {
        $this->cacheManager = $cacheManager;
        $this->metaCompiledDir = $metaCompiledDir;
    }

    public function getMeta($name): Meta
    {
        $cacheManager = $this->cacheManager;

        $list = $this->getList();
        $config = $list[$name] ?? null;

        if (null === $config) {
            throw new \InvalidArgumentException("Meta {$name} not exists. Please reindex meta to refresh new meta lists.");
        }

        $metaConfig = $config['config_file'];

        return $cacheManager->getYamlObject(Meta::class, $metaConfig);
    }

    public function getList(): array
    {
        if (null === $this->list) {
            $cacheManager = $this->cacheManager;
            $metaCompiledDir = $this->metaCompiledDir;
            $file = "{$metaCompiledDir}/index.yaml";
            $this->list = $cacheManager->parseYaml($file);
        }

        return $this->list;
    }
}
