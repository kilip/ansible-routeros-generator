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

namespace RouterOS\Generator\Provider\Ansible\Processor;

use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Contracts\CompilerInterface;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CompileProcessor
{
    /**
     * @var ModuleManagerInterface
     */
    private $moduleManager;
    /**
     * @var CompilerInterface
     */
    private $compiler;
    /**
     * @var CacheManagerInterface
     */
    private $cacheManager;
    /**
     * @var string
     */
    private $targetDir;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ModuleManagerInterface $moduleManager,
        CompilerInterface $compiler,
        CacheManagerInterface $cacheManager,
        string $targetDir
    ) {
        $this->moduleManager = $moduleManager;
        $this->compiler = $compiler;
        $this->cacheManager = $cacheManager;
        $this->targetDir = $targetDir;
        $this->dispatcher = $dispatcher;
    }

    public function process()
    {
        $moduleManager = $this->moduleManager;
        $list = $moduleManager->getList();

        $resources = [];
        foreach ($list as $name => $config) {
            $config = $moduleManager->getConfig($name);
            $this->compileModule($name, $config);
            $this->compileResource($name, $config);

            $resources[$name] = $config['resource'];
        }

        $this->compileSubset($resources);
    }

    public function compileModule($name, $config)
    {
        $compiler = $this->compiler;
        $template = $config['template'];
        $targetDir = $this->targetDir;
        $target = "{$targetDir}/plugins/modules/ros_{$name}.py";
        $compiler->compile($template, $target, $config);

        $dir = \dirname($target);
        if (!file_exists($file = $dir.'/__init__.py')) {
            touch($file);
        }
    }

    public function compileResource($name, $config)
    {
        $compiler = $this->compiler;
        $template = '@ansible/resource.py.twig';
        $package = str_replace('.', '/', $config['package']);
        $targetDir = $this->targetDir;
        $target = "{$targetDir}/plugins/module_utils/resources/{$package}/{$name}.py";
        $compiler->compile($template, $target, $config['resource']);

        $dir = \dirname($target);
        if (!file_exists($file = $dir.'/__init__.py')) {
            touch($file);
        }
    }

    private function compileSubset($resource)
    {
        $compiler = $this->compiler;
        $template = '@ansible/subset.py.twig';
        $targetDir = $this->targetDir;
        $target = "{$targetDir}/plugins/module_utils/resources/subset.py";

        $compiler->compile($template, $target, $resource);
    }
}
