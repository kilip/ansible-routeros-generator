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

namespace RouterOS\Generator\Provider\Ansible\Generator;

use RouterOS\Generator\Contracts\TemplateCompilerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ModuleGenerator
{
    /**
     * @var ModuleManagerInterface
     */
    private $moduleManager;

    /**
     * @var TemplateCompilerInterface
     */
    private $compiler;

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
        TemplateCompilerInterface $compiler,
        string $targetDir
    ) {
        $this->moduleManager = $moduleManager;
        $this->compiler = $compiler;
        $this->targetDir = $targetDir;
        $this->dispatcher = $dispatcher;
    }

    public function createModules()
    {
        $dispatcher = $this->dispatcher;
        $moduleManager = $this->moduleManager;
        $modules = $moduleManager->getModuleList();

        $count = \count($modules);
        foreach ($modules as $module) {
            $name = $module['name'];
            $event = new ProcessEvent(
                'Compiling Module {0}',
                [$name],
                $count
            );
            $dispatcher->dispatch($event, ProcessEvent::EVENT_LOOP);
            $this->createModule($name);
        }
    }

    public function createModule($name)
    {
        $moduleManager = $this->moduleManager;
        $compiler = $this->compiler;
        $module = $moduleManager->findByName($name);
        $config = $module->getConfig();
        $target = $this->targetDir.\DIRECTORY_SEPARATOR.$config['module_name'].'.py';
        $compiler->compile(
            $config['module_template'],
            $target,
            [
                'module' => $module,
                'config' => $config,
                'documentation' => (array) $module->getDocumentation(),
            ]
        );
    }
}
