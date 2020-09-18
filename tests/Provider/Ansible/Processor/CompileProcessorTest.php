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

use PHPUnit\Framework\MockObject\MockObject;
use RouterOS\Generator\Contracts\CompilerInterface;
use RouterOS\Generator\Provider\Ansible\Concerns\InteractsWithAnsibleStructure;
use RouterOS\Generator\Provider\Ansible\Constant;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Provider\Ansible\Processor\CompileProcessor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CompileProcessorTest extends KernelTestCase
{
    use InteractsWithAnsibleStructure;

    private $moduleManager;
    private $compiler;
    private $cacheManager;

    /**
     * @var MockObject|Constant
     */
    private $constant;
    private $processor;
    private $dispatcher;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->moduleManager = $this->createMock(ModuleManagerInterface::class);
        $this->compiler = $this->createMock(CompilerInterface::class);
        $this->cacheManager = $container->get('routeros.util.cache_manager');
        $this->constant = $this->getService('ansible.constant');

        $this->processor = new CompileProcessor(
            $this->dispatcher,
            $this->moduleManager,
            $this->compiler,
            $this->cacheManager,
            $this->constant
        );
    }

    public function testProcess()
    {
        $processor = $this->processor;
        $moduleManager = $this->moduleManager;
        $compiler = $this->compiler;
        $constant = $this->constant;
        $lists = [
            'bridge' => [
                'name' => 'bridge',
            ],
        ];
        $config = $this->getModuleConfig('interface.bridge.bridge');

        $moduleManager->expects($this->once())
            ->method('getList')
            ->willReturn($lists);

        $moduleManager->expects($this->once())
            ->method('getConfig')
            ->with('bridge')
            ->willReturn($config);

        $compiler
            ->expects($this->any())
            ->method('compile')
            ->withConsecutive(
                [
                    $config['template'],
                    $constant->getModulesDir().'/ros_bridge.py',
                    $config,
                ],
                [
                    '@ansible/resource.py.twig',
                    $constant->getResourcesDir().'/interface/bridge/bridge.py',
                    $config['resource'],
                ],
                [
                    '@ansible/tests/facts.yaml.twig',
                    $constant->getModuleTestFactsFixtureDir().'/interface.bridge.bridge.yaml',
                    ['facts' => $config['tests']['facts']],
                ],
                [
                    '@ansible/tests/facts-test.py.twig',
                    $constant->getModuleTestFactsDir().'/test_bridge.py',
                    ['facts' => $config['tests']['facts']],
                ],
                [
                    '@ansible/tests/unit.yaml.twig',
                    $constant->getModuleTestFixtureDir().'/interface.bridge.bridge.yaml',
                    ['unit' => $config['tests']['unit']],
                ],
                [
                    '@ansible/tests/unit-test.py.twig',
                    $constant->getModuleTestDir().'/test_ros_bridge.py',
                    ['unit' => $config['tests']['unit']],
                ],
                [
                    '@ansible/integration/pre_task.yaml.twig',
                    $constant->getModuleIntegrationDir().'/ros_bridge/tests/cli/pre_tasks.yml',
                    ['integration' => $config['integration']],
                ],
                [
                    '@ansible/integration/task.yaml.twig',
                    $constant->getModuleIntegrationDir().'/ros_bridge/tests/cli/merged.yaml',
                    [
                        'name' => 'bridge',
                        'example' => $config['examples'][0],
                    ],
                ],
                [
                    '@ansible/integration/task.yaml.twig',
                    $constant->getModuleIntegrationDir().'/ros_bridge/tests/cli/replaced.yaml',
                    [
                        'name' => 'bridge',
                        'example' => $config['examples'][1],
                    ],
                ],
                [
                    '@ansible/integration/task.yaml.twig',
                    $constant->getModuleIntegrationDir().'/ros_bridge/tests/cli/overridden.yaml',
                    [
                        'name' => 'bridge',
                        'example' => $config['examples'][2],
                    ],
                ],
                [
                    '@ansible/integration/task.yaml.twig',
                    $constant->getModuleIntegrationDir().'/ros_bridge/tests/cli/deleted.yaml',
                    [
                        'name' => 'bridge',
                        'example' => $config['examples'][3],
                    ],
                ],
                [
                    '@ansible/subset.py.twig',
                    $constant->getResourcesDir().'/subset.py',
                    ['modules' => ['bridge' => ['name' => 'bridge']]],
                ]
            );

        $processor->process();
    }
}
