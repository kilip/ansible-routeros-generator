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
use RouterOS\Generator\Concerns\InteractsWithContainer;
use RouterOS\Generator\Event\BuildEvent;
use RouterOS\Generator\Provider\Ansible\Constant;
use RouterOS\Generator\Provider\Ansible\QA\Prepare;
use RouterOS\Generator\Util\ProcessHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PrepareTest extends KernelTestCase
{
    use InteractsWithContainer;

    /**
     * @var MockObject|ProcessHelper
     */
    private $processHelper;

    /**
     * @var Constant
     */
    private $constant;

    /**
     * @var Prepare
     */
    private $listener;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->processHelper = $this->createMock(ProcessHelper::class);
        $this->constant = $this->getService('ansible.constant');

        $this->processHelper
            ->expects($this->any())
            ->method('findExecutable')
            ->willReturnMap([
                ['python3', null, [], 'test-python'],
                ['tox', null, [], 'test-tox'],
                ['pip', null, [], 'test-pip'],
            ]);

        $this->listener = new Prepare(
            $this->dispatcher,
            $this->constant,
            $this->processHelper
        );
    }

    public function testOnPrepare()
    {
        $event = $this->createMock(BuildEvent::class);
        $processHelper = $this->processHelper;
        $listener = $this->listener;

        $processHelper
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($processHelper);

        $processHelper
            ->expects($this->atLeastOnce())
            ->method('run')
            ->willReturn(0);
        $listener->onPrepare($event);
    }
}
