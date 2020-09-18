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

namespace Tests\RouterOS\Generator\Provider\Ansible\QA;

use PHPUnit\Framework\MockObject\MockObject;
use RouterOS\Generator\Concerns\EventSubscriberAssertions;
use RouterOS\Generator\Concerns\InteractsWithContainer;
use RouterOS\Generator\Event\BuildEvent;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Provider\Ansible\QA\Facts;
use RouterOS\Generator\Util\ProcessHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FactsTest extends KernelTestCase
{
    use InteractsWithContainer;
    use EventSubscriberAssertions;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var MockObject|ModuleManagerInterface
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $targetDir;

    /**
     * @var string
     */
    private $modulePrefix;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var Facts
     */
    private $sut;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->moduleManager = $this->createMock(ModuleManagerInterface::class);
        $this->targetDir = sys_get_temp_dir().'/routeros-generator/test';
        $this->processHelper = $this->createMock(ProcessHelper::class);
        $this->subscriberEventClass = Facts::class;

        $this->sut = new Facts(
            $this->dispatcher,
            $this->moduleManager,
            $this->targetDir,
            $this->processHelper
        );
    }

    public function testSubscribedEvents()
    {
        $this->assertSubscribedEvent(BuildEvent::TEST, 'onTest');
    }

    public function testOnTest()
    {
        $event = $this->createMock(BuildEvent::class);
        $processHelper = $this->processHelper;
        $dispatcher = $this->dispatcher;
        $sut = $this->sut;

        $processHelper
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($processHelper);

        /*
        $dispatcher
            ->expects($this->atLeastOnce())
            ->method('dispatch')
            ->with($this->isInstanceOf(ProcessEvent::class));
        */

        $sut->onTest($event);
    }
}
