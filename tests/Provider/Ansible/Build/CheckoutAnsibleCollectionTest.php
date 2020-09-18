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

namespace Tests\RouterOS\Generator\Provider\Ansible\Build;

use PHPUnit\Framework\MockObject\MockObject;
use RouterOS\Generator\Event\BuildEvent;
use RouterOS\Generator\Provider\Ansible\Build\CheckoutAnsibleCollection;
use RouterOS\Generator\Util\ProcessHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CheckoutAnsibleCollectionTest extends KernelTestCase
{
    /**
     * @var MockObject|ProcessHelper
     */
    private $processHelper;

    /**
     * @var \RouterOS\Generator\Provider\Ansible\Build\CheckoutAnsibleCollection
     */
    private $checkout;

    /**
     * @var string
     */
    private $targetDir;

    /**
     * @var string
     */
    private $gitRepository;

    protected function setUp(): void
    {
        $this->processHelper = $this->createMock(ProcessHelper::class);
        $this->targetDir = sys_get_temp_dir().'/routeros-generator/ansible';
        $this->gitRepository = 'some-repo';
        $this->checkout = new CheckoutAnsibleCollection(
            $this->gitRepository,
            $this->targetDir,
            $this->processHelper
        );
    }

    public function testOnPrepare()
    {
        $processHelper = $this->processHelper;
        $checkout = $this->checkout;
        $event = $this->createMock(BuildEvent::class);

        $processHelper
            ->expects($this->atLeastOnce())
            ->method('findExecutable')
            ->willReturnMap([
                ['git', null, [], 'test-git'],
            ]);

        $expectedCommands = [
            'test-git',
            'clone',
            '--branch',
            'wip',
            $this->gitRepository,
            $this->targetDir,
        ];
        $processHelper
            ->expects($this->once())
            ->method('create')
            ->with($expectedCommands)
            ->willReturn($processHelper);
        $processHelper
            ->expects($this->once())
            ->method('run');
        $checkout->onPrepare($event);
    }
}
