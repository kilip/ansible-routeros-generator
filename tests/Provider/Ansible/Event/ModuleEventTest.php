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

namespace Tests\RouterOS\Generator\Provider\Ansible\Event;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Provider\Ansible\Event\ModuleEvent;
use RouterOS\Generator\Provider\Ansible\Structure\ModuleStructure;
use RouterOS\Generator\Structure\ResourceStructure;

class ModuleEventTest extends TestCase
{
    /**
     * @var MockObject|ModuleStructure
     */
    private $module;

    /**
     * @var MockObject|ResourceStructure
     */
    private $resource;

    /**
     * @var ModuleEvent
     */
    private $event;

    protected function setUp(): void
    {
        $this->module = $this->createMock(ModuleStructure::class);
        $this->resource = $this->createMock(ResourceStructure::class);

        $this->module->expects($this->any())
            ->method('getName')
            ->willReturn('test');

        $this->event = new ModuleEvent(
            $this->module,
            $this->resource
        );
    }

    public function testConstruct()
    {
        $event = $this->event;

        $this->assertSame($this->module, $event->getModule());
        $this->assertSame($this->resource, $event->getResource());
    }

    public function testAddConfig()
    {
        $event = $this->event;

        $event->addConfig(['hello' => 'world']);

        $config = $event->getConfig();
        $this->assertSame('world', $config['hello']);
    }
}
