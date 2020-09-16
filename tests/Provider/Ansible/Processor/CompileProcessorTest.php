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

namespace Tests\RouterOS\Generator\Provider\Ansible\Processor;

use RouterOS\Generator\Contracts\CompilerInterface;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Provider\Ansible\Processor\CompileProcessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tests\RouterOS\Generator\Concerns\InteractsWithContainer;

class CompileProcessorTest extends KernelTestCase
{
    use InteractsWithContainer;

    private $moduleManager;
    private $compiler;
    private $cacheManager;
    private $targetDir;
    private $processor;
    private $dispatcher;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->moduleManager = $this->createMock(ModuleManagerInterface::class);
        $this->compiler = $this->createMock(CompilerInterface::class);
        $this->cacheManager = $container->get('routeros.util.cache_manager');
        $this->targetDir = $container->getParameter('ansible.target_dir');

        $this->processor = new CompileProcessor(
            $this->dispatcher,
            $this->moduleManager,
            $this->compiler,
            $this->cacheManager,
            $this->targetDir
        );
    }

    public function testProcess()
    {
        $processor = $this->processor;
        $moduleManager = $this->moduleManager;
        $compiler = $this->compiler;
        $lists = [
            'bridge' => [
                'name' => 'bridge',
            ],
        ];
        $config = [
            'name' => 'bridge',
            'package' => 'interface.bridge',
            'documentation' => [],
            'examples' => [],
            'template' => '@ansible/module/module.py.twig',
            'resource' => ['resource'],
        ];

        $moduleManager->expects($this->once())
            ->method('getList')
            ->willReturn($lists);

        $moduleManager->expects($this->once())
            ->method('getConfig')
            ->with('bridge')
            ->willReturn($config);

        $compiler
            ->expects($this->exactly(3))
            ->method('compile')
            ->withConsecutive(
                [
                    $config['template'],
                    $this->targetDir.'/plugins/modules/ros_bridge.py',
                    $config,
                ],
                [
                    '@ansible/resource.py.twig',
                    $this->targetDir.'/plugins/module_utils/resources/interface/bridge/bridge.py',
                    $config['resource'],
                ],
                [
                    '@ansible/subset.py.twig',
                    $this->targetDir.'/plugins/module_utils/resources/subset.py',
                    ['modules' => ['bridge' => ['name' => 'bridge']]],
                ]
            );

        $processor->process();
    }
}
