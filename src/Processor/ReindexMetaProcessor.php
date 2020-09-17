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

namespace RouterOS\Generator\Processor;

use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Contracts\CompilerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Util\Text;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ReindexMetaProcessor
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
     * @var string
     */
    private $metaConfigDir;
    /**
     * @var string
     */
    private $metaCompiledDir;
    /**
     * @var ConfigurationInterface
     */
    private $metaConfiguration;
    /**
     * @var CompilerInterface
     */
    private $templateCompiler;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CacheManagerInterface $cacheManager,
        ConfigurationInterface $metaConfiguration,
        CompilerInterface $templateCompiler,
        string $metaConfigDir,
        string $metaCompiledDir
    ) {
        $this->dispatcher = $dispatcher;
        $this->cacheManager = $cacheManager;
        $this->metaConfigDir = $metaConfigDir;
        $this->metaCompiledDir = $metaCompiledDir;
        $this->metaConfiguration = $metaConfiguration;
        $this->templateCompiler = $templateCompiler;
    }

    public function process()
    {
        $dispatcher = $this->dispatcher;
        $cacheManager = $this->cacheManager;
        $path = $this->metaConfigDir;
        $configuration = $this->metaConfiguration;
        $metaCompiledDir = $this->metaCompiledDir;
        $templateCompiler = $this->templateCompiler;
        $sortOrders = [
            'properties_override',
            'options',
            'generator',
            'keys',
            'command',
            'type',
            'config_file',
            'package',
            'name',
        ];

        $metas = $cacheManager->processYamlConfig(
            $configuration,
            $path
        );

        $event = new ProcessEvent('Reindex Meta Started', [], \count($metas));
        $dispatcher->dispatch($event, ProcessEvent::EVENT_START);
        $index = [];
        foreach ($metas as $name => $config) {
            $event = new ProcessEvent('Processing {0}', [$name]);
            $dispatcher->dispatch($event, ProcessEvent::EVENT_LOOP);

            $config['name'] = $name;
            $package = $config['package'];
            $package = str_replace('.', '/', $package);
            $target = "{$metaCompiledDir}/{$package}/{$name}.yaml";
            $index[$name] = [
                'name' => $name,
                'config_file' => $target,
            ];

            $config = Text::arrayKeySort($sortOrders, $config);

            $templateCompiler->compileYaml($config, $target);
        }

        $target = "{$metaCompiledDir}/index.yaml";
        $templateCompiler->compileYaml($index, $target);
    }
}
