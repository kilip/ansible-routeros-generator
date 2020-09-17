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
use RouterOS\Generator\Event\BuildEvent;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CompileProcessor implements EventSubscriberInterface
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

    public static function getSubscribedEvents()
    {
        return [
            BuildEvent::BUILD => ['onBuild'],
        ];
    }

    public function onBuild(BuildEvent $event)
    {
        $event->getOutput()->writeln('<info>Generating Ansible Modules</info>');
    }

    public function process()
    {
        $moduleManager = $this->moduleManager;
        $list = $moduleManager->getList();
        $dispatcher = $this->dispatcher;
        $targetDir = $this->targetDir;
        $resources = [];

        $processEvent = new ProcessEvent('Starting...', []);
        $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_START);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        foreach ($list as $name => $config) {
            $processEvent->setMessage('Processing {0}')->setContext([$name]);
            $config = $moduleManager->getConfig($name);
            $this->compileModule($name, $config);
            $this->compileResource($name, $config);
            $this->compileFactsTests($name, $config);
            $this->compileUnitTests($name, $config);
            $this->compileIntegration($name, $config);

            $resources[$name] = $config['resource'];
        }

        $processEvent->setMessage('Completed');
        $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_END);

        $this->compileSubset($list);
    }

    public function compileModule($name, $config)
    {
        $compiler = $this->compiler;
        $template = $config['template'];
        $targetDir = $this->targetDir;
        $target = "{$targetDir}/plugins/modules/ros_{$name}.py";
        $compiler->compile($template, $target, $config);

        $file = \dirname($target).'/__init__.py';
        filesystem()->ensureFileExists($file);
    }

    public function compileResource($name, $config)
    {
        $compiler = $this->compiler;
        $template = '@ansible/resource.py.twig';
        $package = str_replace('.', '/', $config['package']);
        $targetDir = $this->targetDir;
        $target = "{$targetDir}/plugins/module_utils/resources/{$package}/{$name}.py";
        $compiler->compile($template, $target, $config['resource']);

        $file = \dirname($target).'/__init__.py';
        filesystem()->ensureFileExists($file);
    }

    public function compileFactsTests($name, $config)
    {
        $compiler = $this->compiler;
        $template = '@ansible/tests/facts.yaml.twig';
        $package = $config['package'];
        $targetDir = $this->targetDir;
        $target = "{$targetDir}/tests/unit/modules/fixtures/facts/{$package}.{$name}.yaml";
        $compiler->compile($template, $target, [
            'facts' => $config['tests']['facts'],
        ]);
    }

    private function compileSubset(array $list)
    {
        $compiler = $this->compiler;
        $template = '@ansible/subset.py.twig';
        $targetDir = $this->targetDir;
        $target = "{$targetDir}/plugins/module_utils/resources/subset.py";

        $compiler->compile($template, $target, [
            'modules' => $list,
        ]);
    }

    private function compileUnitTests($name, array $config)
    {
        $compiler = $this->compiler;
        $template = '@ansible/tests/unit.yaml.twig';
        $package = $config['package'];
        $targetDir = $this->targetDir;
        $target = "{$targetDir}/tests/unit/modules/fixtures/units/{$package}.{$name}.yaml";
        $compiler->compile($template, $target, [
            'unit' => $config['tests']['unit'],
        ]);
    }

    private function compileIntegration($name, array $config)
    {
        $compiler = $this->compiler;
        $targetDir = $this->targetDir;
        $moduleName = 'ros_'.$name;
        $targetCli = "{$targetDir}/tests/integration/targets/{$moduleName}/tests/cli";

        if (isset($config['integration'])) {
            $integration = $config['integration'];
            $template = '@ansible/integration/pre_task.yaml.twig';
            $target = "{$targetCli}/pre_tasks.yml";
            $compiler->compile($template, $target, ['integration' => $integration]);
        }

        if (isset($config['examples'])) {
            $examples = $config['examples'];
            $template = '@ansible/integration/task.yaml.twig';
            foreach ($examples as $example) {
                $state = $example['argument_spec']['state'];
                $target = "{$targetCli}/{$state}.yaml";
                $compiler->compile($template, $target, [
                    'name' => $name,
                    'example' => $example,
                ]);
            }
        }
    }
}
