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

namespace RouterOS\Generator\Provider\Ansible\QA;

use RouterOS\Generator\Concerns\EventSubscriberAssertions;
use RouterOS\Generator\Concerns\InteractsWithContainer;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Provider\Ansible\Event\AnsibleTestEvent;
use RouterOS\Generator\Util\ProcessHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UnitTest extends KernelTestCase
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
     * @var Constant
     */
    private $constant;

    /**
     * @var MockObject|ProcessHelper
     */
    private $processHelper;

    /**
     * @var Unit
     */
    private $sut;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->moduleManager = $this->createMock(ModuleManagerInterface::class);
        $this->constant = $this->getService('ansible.constant');
        $this->processHelper = $this->createMock(ProcessHelper::class);
        $this->subscriberEventClass = Unit::class;

        $this->sut = new Unit(
            $this->dispatcher,
            $this->moduleManager,
            $this->constant,
            $this->processHelper
        );
    }

    public function testSubscribedEvent()
    {
        $this->assertSubscribedEvent(AnsibleTestEvent::ALL, 'onRun');
    }

    public function testOnRun()
    {
        $dispatcher = $this->dispatcher;
        $moduleManager = $this->moduleManager;
        $processHelper = $this->processHelper;
        $event = $this->createMock(AnsibleTestEvent::class);
        $sut = $this->sut;

        $moduleManager
            ->expects($this->once())
            ->method('getList')
            ->willReturn([
                'bridge' => [],
                'interface' => [],
            ]);

        $processHelper
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($processHelper);

        $processHelper
            ->expects($this->exactly(2))
            ->method('run')
            ->willReturnOnConsecutiveCalls(0, 1);

        $dispatcher
            ->expects($this->exactly(5))
            ->method('dispatch');

        $sut->onRun($event);
    }
}
