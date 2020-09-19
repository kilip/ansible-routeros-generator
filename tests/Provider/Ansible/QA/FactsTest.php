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
use RouterOS\Generator\Provider\Ansible\Constant;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Provider\Ansible\Event\AnsibleTestEvent;
use RouterOS\Generator\Provider\Ansible\QA\Facts;
use RouterOS\Generator\Util\ProcessHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Process\Process;
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
     * @var Constant
     */
    private $constant;

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
        $this->constant = $this->getService('ansible.constant');
        $this->processHelper = $this->createMock(ProcessHelper::class);
        $this->subscriberEventClass = Facts::class;

        $this->sut = new Facts(
            $this->dispatcher,
            $this->moduleManager,
            $this->constant,
            $this->processHelper
        );
    }

    public function testSubscribedEvents()
    {
        $this->assertSubscribedEvent(AnsibleTestEvent::ALL, 'onAnsibleTest');
    }

    public function testOnTest()
    {
        $event = $this->createMock(AnsibleTestEvent::class);
        $moduleManager = $this->moduleManager;
        $processHelper = $this->processHelper;
        $dispatcher = $this->dispatcher;
        $sut = $this->sut;
        $list = ['bridge' => [], 'interface' => []];

        $moduleManager->expects($this->once())
            ->method('getList')
            ->willReturn($list);

        $processHelper
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($processHelper);

        $processHelper
            ->expects($this->atLeastOnce())
            ->method('run')
            ->willReturnOnConsecutiveCalls(
                0,
                1
            );

        $process = new Process([]);

        $processHelper
            ->expects($this->once())
            ->method('getProcess')
            ->willReturn($process);

        $sut->onAnsibleTest($event);
    }
}
