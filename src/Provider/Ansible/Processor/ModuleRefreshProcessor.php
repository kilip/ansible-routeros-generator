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
use RouterOS\Generator\Contracts\ResourceManagerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Provider\Ansible\Event\ModuleEvent;
use RouterOS\Generator\Provider\Ansible\Structure\ModuleStructure;
use RouterOS\Generator\Structure\ResourceStructure;
use RouterOS\Generator\Util\Text;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ModuleRefreshProcessor
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var CacheManagerInterface
     */
    private $cacheManager;
    /**
     * @var CompilerInterface
     */
    private $templateCompiler;
    /**
     * @var ResourceManagerInterface
     */
    private $resourceManager;
    /**
     * @var string
     */
    private $moduleCompiledDir;
    /**
     * @var string
     */
    private $moduleConfigDir;
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CacheManagerInterface $cacheManager,
        CompilerInterface $templateCompiler,
        ResourceManagerInterface $resourceManager,
        ConfigurationInterface $configuration,
        string $moduleConfigDir,
        string $moduleCompiledDir
    ) {
        $this->dispatcher = $dispatcher;
        $this->cacheManager = $cacheManager;
        $this->templateCompiler = $templateCompiler;
        $this->resourceManager = $resourceManager;
        $this->moduleCompiledDir = $moduleCompiledDir;
        $this->moduleConfigDir = $moduleConfigDir;
        $this->configuration = $configuration;
    }

    public function process()
    {
        $dispatcher = $this->dispatcher;
        $cacheManager = $this->cacheManager;
        $configuration = $this->configuration;
        $moduleConfigDir = $this->moduleConfigDir;
        $resourceManager = $this->resourceManager;

        $modules = $cacheManager->processYamlConfig(
            $configuration,
            $moduleConfigDir
        );

        $processEvent = new ProcessEvent(
            'Start processing ansible modules',
            [],
            \count($modules)
        );
        $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_START);

        $index = [];

        foreach ($modules as $name => $config) {
            $resource = $resourceManager->getResource($name);
            $module = $this->configureModule($resource, $config);
            $module->setName($name);

            $processEvent->setMessage('Processing {0}')->setContext([$name]);
            $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_LOOP);

            $moduleEvent = new ModuleEvent($module, $resource);
            $dispatcher->dispatch($moduleEvent, ModuleEvent::PRE_COMPILE);

            $target = $this->compile($module, $moduleEvent->getConfig());
            $index[$name] = [
                'name' => $name,
                'package' => $resource->getPackage(),
                'resource_class' => Text::classify($name).'Resource',
                'config_file' => $target,
            ];
        }
        $processEvent->setMessage('End Process');
        $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_END);

        $this->compileIndex($index);
    }

    private function configureModule(ResourceStructure $resource, $config): ModuleStructure
    {
        $module = new ModuleStructure();
        $module->fromArray($config);

        if (null === $module->getDefaultState()) {
            if ('config' == $resource->getType()) {
                $module->setDefaultState('merged');
            } else {
                $module->setDefaultState('present');
            }
        }

        return $module;
    }

    private function compile(ModuleStructure $module, $config)
    {
        $templateCompiler = $this->templateCompiler;
        $moduleCompiledDir = $this->moduleCompiledDir;
        $package = str_replace('.', '/', $module->getPackage());
        $target = "{$moduleCompiledDir}/{$package}/{$module->getName()}.yaml";

        $templateCompiler->compileYaml($config, $target);

        return $target;
    }

    private function compileIndex(array $index)
    {
        $templateCompiler = $this->templateCompiler;
        $moduleCompiledDir = $this->moduleCompiledDir;
        $target = "{$moduleCompiledDir}/index.yaml";

        ksort($index);
        $templateCompiler->compileYaml($index, $target);
    }
}
