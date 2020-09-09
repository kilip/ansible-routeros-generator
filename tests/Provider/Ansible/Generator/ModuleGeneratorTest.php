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
use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Contracts\TemplateCompilerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Provider\Ansible\Generator\ModuleGenerator;
use RouterOS\Generator\Provider\Ansible\Model\Module;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ModuleGeneratorTest extends TestCase
{
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
