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

namespace Tests\RouterOS\Generator\Provider\Ansible\Config;

use PHPUnit\Framework\MockObject\MockObject;
use RouterOS\Generator\Provider\Ansible\Concerns\InteractsWithAnsibleStructure;
use RouterOS\Generator\Provider\Ansible\Config\IntegrationConfig;
use RouterOS\Generator\Provider\Ansible\Event\ModuleEvent;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IntegrationConfigTest extends KernelTestCase
{
    use InteractsWithAnsibleStructure;

    /**
     * @var MockObject|ModuleEvent
     */
    private $event;

    protected function setUp(): void
    {
        $module = $this->createModule('interface.interface');
        $resource = $this->createResource('interface.interface');
        $this->event = $this->createMock(ModuleEvent::class);

        $this->event
            ->expects($this->any())
            ->method('getModule')
            ->willReturn($module);
        $this->event
            ->expects($this->any())
            ->method('getResource')
            ->willReturn($resource);
    }

    public function testOnPreCompile()
    {
        $event = $this->event;
        $module = $event->getModule();

        $interaction = new IntegrationConfig();

        $event->expects($this->once())
            ->method('addConfig')
            ->with(['integration' => $module->getIntegration()]);

        $interaction->onPreCompile($event);
    }
}
