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

namespace Tests\RouterOS\Generator\Provider\Ansible\Generator;

use PHPUnit\Framework\MockObject\MockObject;
use RouterOS\Generator\Contracts\TemplateCompilerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Provider\Ansible\Generator\ModuleGenerator;
use RouterOS\Generator\Provider\Ansible\Model\Module;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tests\RouterOS\Generator\Concerns\InteractsWithContainer;
use Tests\RouterOS\Generator\Concerns\InteractsWithFilesystem;

class ModuleGeneratorTest extends KernelTestCase
{
    use InteractsWithContainer;
    use InteractsWithFilesystem;

    /**
     * @var MockObject|ModuleManagerInterface
     */
    private $moduleManager;

    /**
     * @var MockObject|TemplateCompilerInterface
     */
    private $templateCompiler;

    /**
     * @var ModuleGenerator
     */
    private $generator;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $targetDir;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->moduleManager = $this->createMock(ModuleManagerInterface::class);
        $this->templateCompiler = $this->createMock(TemplateCompilerInterface::class);

        $this->generator = new ModuleGenerator(
            $this->dispatcher,
            $this->moduleManager,
            $this->templateCompiler,
            __DIR__.'/../Fixtures/compiled'
        );
    }

    public function testCreateModule()
    {
        $generator = $this->generator;
        $compiler = $this->templateCompiler;

        $this->configureMock();

        $compiler->expects($this->once())
            ->method('compile')
            ->with(
                '@ansible/module/custom.py.twig',
                __DIR__.'/../Fixtures/compiled/module_name.py',
                $this->callback(function ($v) {
                    if (!isset($v['module'])) {
                        return false;
                    }
                    if (!isset($v['config'])) {
                        return false;
                    }

                    return true;
                })
            );
        $generator->createModule('bridge');
    }

    public function testCreateModules()
    {
        $moduleManager = $this->moduleManager;
        $dispatcher = $this->dispatcher;
        $generator = $this->generator;

        $this->configureMock();

        $moduleManager->expects($this->once())
            ->method('getModuleList')
            ->willReturn([
                [
                    'name' => 'bridge',
                ],
            ]);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(ProcessEvent::class),
                ProcessEvent::EVENT_LOOP
            );

        $generator->createModules();
    }

    /**
     * @param string $module
     * @param string $pattern
     * @dataProvider getCompiledTestData
     */
    public function testModuleCompile($module, $pattern)
    {
        $contents = $this->getCompiledContents($module);
        $pattern = "#{$pattern}#";
        $this->assertRegExp($pattern, $contents, $pattern);
    }

    public function getCompiledTestData()
    {
        return [
            ['bridge', 'AUTO GENERATED CODE'],
            ['bridge', '^\#!/usr/bin/python'],
            ['bridge', 'from __future__ import absolute_import, division, print_function'],
            ['bridge', '__metaclass__ = type'],
            ['bridge', 'DOCUMENTATION = """'],
            ['bridge', 'EXAMPLES = """'],
            ['bridge', 'RETURNS = """'],
            ['bridge', 'The module file for kilip.routeros.ros_bridge'],
        ];
    }

    private function getCompiledContents($module)
    {
        static $contents = [];

        if (!isset($contents[$module])) {
            $targetDir = $this->getContainer()->getParameter('ansible.target_dir');
            $target = "{$targetDir}/ros_{$module}.py";
            if (is_file($target)) {
                unlink($target);
            }
            $scrap = $this->getContainer()->get('routeros.scraper.documentation');
            $scrap->start();
            $loader = $this->getContainer()->get('ansible.config_loader');
            $loader->refresh();

            $generator = $this->getContainer()->get('ansible.generator.module');
            $generator->createModule($module);
            $contents[$module] = file_get_contents($target);
        }

        return $contents[$module];
    }

    private function configureMock()
    {
        $moduleManager = $this->moduleManager;
        $bridge = new Module();

        $bridge->setName('bridge');
        $bridge->setConfig([
            'module_name' => 'module_name',
            'module_template' => '@ansible/module/custom.py.twig',
        ]);

        $moduleManager->expects($this->once())
            ->method('findByName')
            ->with('bridge')
            ->willReturn($bridge);
    }
}
