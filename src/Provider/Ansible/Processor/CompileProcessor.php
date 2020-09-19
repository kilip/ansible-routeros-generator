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
use RouterOS\Generator\Provider\Ansible\Constant;
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
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var Constant
     */
    private $constant;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ModuleManagerInterface $moduleManager,
        CompilerInterface $compiler,
        CacheManagerInterface $cacheManager,
        Constant $constant
    ) {
        $this->moduleManager = $moduleManager;
        $this->compiler = $compiler;
        $this->cacheManager = $cacheManager;
        $this->dispatcher = $dispatcher;
        $this->constant = $constant;
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
        $this->process();
    }

    public function process()
    {
        $moduleManager = $this->moduleManager;
        $list = $moduleManager->getList();
        $dispatcher = $this->dispatcher;
        $constant = $this->constant;
        $resources = [];

        $processEvent = new ProcessEvent('Starting...', []);
        $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_START);

        filesystem()->ensureDirExists($constant->getTargetDir());

        foreach ($list as $name => $config) {
            $processEvent->setMessage('Processing {0}')->setContext([$name]);
            $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_LOOP);

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
        $constant = $this->constant;
        $target = "{$constant->getModulesDir()}/ros_{$name}.py";
        $compiler->compile($template, $target, $config);

        $file = \dirname($target).'/__init__.py';
        filesystem()->ensureFileExists($file);
    }

    public function compileResource($name, $config)
    {
        $compiler = $this->compiler;
        $template = '@ansible/resource.py.twig';
        $package = str_replace('.', '/', $config['package']);
        $constant = $this->constant;
        $target = "{$constant->getResourcesDir()}/{$package}/{$name}.py";
        $compiler->compile($template, $target, $config['resource']);

        $file = \dirname($target).'/__init__.py';
        filesystem()->ensureFileExists($file);
    }

    public function compileFactsTests($name, $config)
    {
        $compiler = $this->compiler;
        $template = '@ansible/tests/facts.yaml.twig';
        $package = $config['package'];
        $constant = $this->constant;

        $target = "{$constant->getModuleTestFactsFixtureDir()}/{$package}.{$name}.yaml";
        $compiler->compile($template, $target, [
            'facts' => $config['tests']['facts'],
        ]);

        $target = "{$constant->getModuleTestFactsDir()}/test_{$name}.py";
        $template = '@ansible/tests/facts-test.py.twig';
        $compiler->compile($template, $target, [
            'facts' => $config['tests']['facts'],
        ]);
    }

    private function compileSubset(array $list)
    {
        $compiler = $this->compiler;
        $template = '@ansible/subset.py.twig';
        $constant = $this->constant;
        $target = "{$constant->getResourcesDir()}/subset.py";

        $compiler->compile($template, $target, [
            'modules' => $list,
        ]);
    }

    private function compileUnitTests($name, array $config)
    {
        $compiler = $this->compiler;
        $package = $config['package'];
        $constant = $this->constant;

        $template = '@ansible/tests/unit.yaml.twig';
        $target = "{$constant->getModuleTestFixtureDir()}/{$package}.{$name}.yaml";
        $compiler->compile($template, $target, [
            'unit' => $config['tests']['unit'],
        ]);

        $template = '@ansible/tests/unit-test.py.twig';
        $target = "{$constant->getModuleTestDir()}/test_ros_{$name}.py";
        $compiler->compile($template, $target, [
            'unit' => $config['tests']['unit'],
        ]);
    }

    private function compileIntegration($name, array $config)
    {
        $compiler = $this->compiler;
        $constant = $this->constant;
        $moduleName = 'ros_'.$name;
        $targetDir = "{$constant->getModuleIntegrationDir()}/{$moduleName}";
        $targetCli = "{$targetDir}/tests/cli";

        if (isset($config['integration']) && isset($config['examples'])) {
            $integration = $config['integration'];
            $template = '@ansible/integration/pre_task.yaml.twig';
            $target = "{$targetCli}/pre_tasks.yml";
            $compiler->compile($template, $target, ['integration' => $integration]);

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

            $files = [
                'defaults-main.yaml',
                'meta-main.yaml',
                'tasks-cli.yaml',
                'tasks-main.yaml',
            ];
            foreach ($files as $file) {
                $template = "@ansible/integration/{$file}";
                $filePath = str_replace('-', '/', $file);
                $target = "{$targetDir}/{$filePath}";

                $compiler->compile($template, $target, []);
            }
        }
    }
}
