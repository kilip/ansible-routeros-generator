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

namespace Tests\RouterOS\Generator\Provider\Ansible;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Contracts\SubMenuManagerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Model\SubMenu;
use RouterOS\Generator\Provider\Ansible\ConfigLoader;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Provider\Ansible\Model\Module;
use RouterOS\Generator\Provider\Ansible\ModuleConfiguration;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConfigLoaderTest extends TestCase
{
    /**
     * @var string
     */
    private $modulesConfigDir;

    /**
     * @var ModuleConfiguration
     */
    private $configuration;

    /**
     * @var MockObject|ModuleManagerInterface
     */
    private $moduleManager;

    /**
     * @var MockObject|SubMenuManagerInterface
     */
    private $subMenuManager;

    /**
     * @var MockObject|CacheManagerInterface
     */
    private $cache;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ConfigLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->cache = $this->createMock(CacheManagerInterface::class);
        $this->moduleManager = $this->createMock(ModuleManagerInterface::class);
        $this->subMenuManager = $this->createMock(SubMenuManagerInterface::class);
        $this->configuration = new ModuleConfiguration();
        $this->modulesConfigDir = __DIR__.'/Fixtures/modules';

        $this->loader = new ConfigLoader(
            $this->dispatcher,
            $this->cache,
            $this->moduleManager,
            $this->subMenuManager,
            $this->configuration,
            $this->modulesConfigDir
        );
    }

    public function testRefresh()
    {
        $configuration = $this->configuration;
        $cache = $this->cache;
        $loader = $this->loader;
        $config = $this->getConfig();
        $moduleManager = $this->moduleManager;
        $subMenuManager = $this->subMenuManager;
        $module = $this->createMock(Module::class);
        $subMenu = $this->createMock(SubMenu::class);

        $cache->expects($this->once())
            ->method('processYamlConfig')
            ->with(
                $configuration,
                'ansible.modules',
                $this->modulesConfigDir,
                true
            )
            ->willReturn($config);

        $this->configureEventAssert();
        $moduleManager
            ->expects($this->once())
            ->method('findOrCreate')
            ->with('interface')
            ->willReturn($module);

        $subMenuManager->expects($this->once())
            ->method('findByName')
            ->with('interface')
            ->willReturn($subMenu);

        $module->expects($this->once())
            ->method('setSubMenu')
            ->with($subMenu);

        $moduleManager
            ->expects($this->once())
            ->method('update')
            ->with($module);

        $loader->refresh();
    }

    private function getConfig()
    {
        $file = $this->modulesConfigDir.'/interface.yml';
        $config = Yaml::parseFile($file);
        $config['config_file'] = $file;

        return [
            'modules' => [
                'interface' => $config,
            ],
        ];
    }

    private function configureEventAssert()
    {
        $dispatcher = $this->dispatcher;

        $dispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(ProcessEvent::class),
                ProcessEvent::EVENT_START
            );
        $dispatcher->expects($this->at(1))
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(ProcessEvent::class),
                ProcessEvent::EVENT_LOOP
            );
        $dispatcher->expects($this->at(2))
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(ProcessEvent::class),
                ProcessEvent::EVENT_END
            );
    }
}
