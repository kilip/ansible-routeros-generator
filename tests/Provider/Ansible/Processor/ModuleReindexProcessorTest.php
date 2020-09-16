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
use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Contracts\CompilerInterface;
use RouterOS\Generator\Contracts\ResourceManagerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Provider\Ansible\Event\ModuleEvent;
use RouterOS\Generator\Provider\Ansible\Processor\ModuleRefreshProcessor;
use RouterOS\Generator\Structure\ResourceStructure;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tests\RouterOS\Generator\Concerns\InteractsWithContainer;

class ModuleReindexProcessorTest extends KernelTestCase
{
    use InteractsWithContainer;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var MockObject|CacheManagerInterface
     */
    private $cacheManager;

    /**
     * @var MockObject|CompilerInterface
     */
    private $compiler;

    /**
     * @var MockObject|ResourceManagerInterface
     */
    private $resourceManager;

    /**
     * @var MockObject|ConfigurationInterface
     */
    private $configuration;

    /**
     * @var string
     */
    private $moduleCompiledDir;

    /**
     * @var string
     */
    private $moduleConfigDir;

    /**
     * @var ModuleRefreshProcessor
     */
    private $processor;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->cacheManager = $this->createMock(CacheManagerInterface::class);
        $this->compiler = $this->createMock(CompilerInterface::class);
        $this->resourceManager = $this->createMock(ResourceManagerInterface::class);
        $this->moduleConfigDir = realpath(__DIR__.'/../../../Fixtures/etc/ansible/modules');
        $this->moduleCompiledDir = $this->getContainer()->getParameter('ansible.compiled_dir');
        $this->configuration = $this->createMock(ConfigurationInterface::class);

        $this->processor = new ModuleRefreshProcessor(
            $this->dispatcher,
            $this->cacheManager,
            $this->compiler,
            $this->resourceManager,
            $this->configuration,
            $this->moduleConfigDir,
            $this->moduleCompiledDir
        );
    }

    public function testProcess()
    {
        $cacheManager = $this->cacheManager;
        $resourceManager = $this->resourceManager;
        $processor = $this->processor;
        $dispatcher = $this->dispatcher;
        $compiler = $this->compiler;

        $resource = new ResourceStructure();
        $resource
            ->setCommand('/interface')
            ->setPackage('interface');
        $config = [
            'interface' => $this->getConfig('interface'),
        ];

        $resourceManager
            ->expects($this->once())
            ->method('getResource')
            ->with('interface')
            ->willReturn($resource);

        $cacheManager
            ->expects($this->once())
            ->method('processYamlConfig')
            ->with(
                $this->configuration,
                'modules',
                $this->moduleConfigDir
            )
            ->willReturn($config);

        $compiler
            ->expects($this->exactly(2))
            ->method('compileYaml')
            ->withConsecutive(
                [
                    $this->isType('array'),
                    $this->moduleCompiledDir.'/interface/interface.yaml',
                ],
                [
                    $this->isType('array'),
                    $this->moduleCompiledDir.'/index.yaml',
                ]
            );

        $this->configureDispatcherExpectations($dispatcher);

        $processor->process();
    }

    private function getConfig($file)
    {
        return Yaml::parseFile("{$this->moduleConfigDir}/{$file}.yaml");
    }

    private function configureDispatcherExpectations(MockObject $dispatcher)
    {
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(ProcessEvent::class),
                ProcessEvent::EVENT_START
            );
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(ProcessEvent::class),
                ProcessEvent::EVENT_LOOP
            );
        $dispatcher->expects($this->at(2))
            ->method('dispatch')
            ->with($this->isInstanceOf(ModuleEvent::class), ModuleEvent::PRE_COMPILE);

        $dispatcher
            ->expects($this->at(3))
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(ProcessEvent::class),
                ProcessEvent::EVENT_END
            );
    }
}
