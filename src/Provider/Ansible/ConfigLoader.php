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

namespace RouterOS\Generator\Provider\Ansible;

use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Contracts\SubMenuManagerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConfigLoader
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var CacheManagerInterface
     */
    private $cache;
    /**
     * @var ModuleManagerInterface
     */
    private $moduleManager;
    /**
     * @var ModuleConfiguration
     */
    private $moduleConfiguration;
    /**
     * @var string
     */
    private $modulesConfigDir;
    /**
     * @var SubMenuManagerInterface
     */
    private $subMenuManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CacheManagerInterface $cache,
        ModuleManagerInterface $moduleManager,
        SubMenuManagerInterface $subMenuManager,
        ModuleConfiguration $moduleConfiguration,
        string $modulesConfigDir
    ) {
        $this->dispatcher = $dispatcher;
        $this->cache = $cache;
        $this->moduleManager = $moduleManager;
        $this->moduleConfiguration = $moduleConfiguration;
        $this->modulesConfigDir = $modulesConfigDir;
        $this->subMenuManager = $subMenuManager;
    }

    public function refresh()
    {
        $dispatcher = $this->dispatcher;
        $modules = $this->getModules();
        $count = \count($modules);
        $event = new ProcessEvent('Start Reindexing Modules', [], $count);

        $dispatcher->dispatch($event, ProcessEvent::EVENT_START);
        foreach ($modules as $name => $config) {
            $event = new ProcessEvent(
                'Processing {0}',
                [$name],
                $count
            );
            $dispatcher->dispatch($event, ProcessEvent::EVENT_LOOP);

            $this->process($name, $config);
        }
        $dispatcher->dispatch($event, ProcessEvent::EVENT_END);
    }

    private function getModules()
    {
        $cache = $this->cache;
        $configuration = $this->moduleConfiguration;
        $modulesConfigDir = $this->modulesConfigDir;

        $config = $cache->processYamlConfig(
            $configuration,
            'ansible.modules',
            $modulesConfigDir,
            true
        );

        return $config['modules'];
    }

    private function process($name, $config)
    {
        $moduleManager = $this->moduleManager;
        $subMenuManager = $this->subMenuManager;
        $configFile = $config['config_file'];

        $module = $moduleManager->findOrCreate($name);
        $subMenu = $subMenuManager->findByName($name);

        $config['module_name'] = 'ros_'.$name;
        $module->setSubMenu($subMenu);
        $module->setConfig($config);
        $module->setConfigFile($configFile);

        $moduleManager->update($module);
    }
}
