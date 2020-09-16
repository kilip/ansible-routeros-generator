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

namespace RouterOS\Generator\Provider\Ansible\Structure;

use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;

class ModuleManager implements ModuleManagerInterface
{
    /**
     * @var CacheManagerInterface
     */
    private $cacheManager;

    /**
     * @var string
     */
    private $ansibleCompiledDir;

    /**
     * @var array
     */
    private $list = [];

    public function __construct(
        CacheManagerInterface $cacheManager,
        string $ansibleCompiledDir
    ) {
        $this->cacheManager = $cacheManager;
        $this->ansibleCompiledDir = $ansibleCompiledDir;
    }

    public function getConfig($name): array
    {
        $list = $this->getList();
        if (!isset($list[$name])) {
            throw new \InvalidArgumentException("Ansible compiled config {$name} not found. Please use ansible:refresh command to refresh compiled modules.");
        }

        $cacheManager = $this->cacheManager;
        $file = $list[$name]['config_file'];

        return $cacheManager->parseYaml($file);
    }

    public function getList(): array
    {
        if (empty($this->list)) {
            $cacheManager = $this->cacheManager;
            $indexFile = $this->ansibleCompiledDir.'/index.yaml';

            $this->list = $cacheManager->parseYaml($indexFile);
        }

        return $this->list;
    }
}
