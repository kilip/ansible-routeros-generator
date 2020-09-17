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

namespace Tests\RouterOS\Generator\Provider\Ansible\Listener;

use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Provider\Ansible\Config\DocumentationConfig;
use RouterOS\Generator\Provider\Ansible\Event\ModuleEvent;
use RouterOS\Generator\Provider\Ansible\Structure\ModuleStructure;
use RouterOS\Generator\Structure\ResourceStructure;
use Symfony\Component\Yaml\Yaml;

class ModuleDocumentationListenerTest extends TestCase
{
    public function testOnPreCompile()
    {
        $module = $this->createModule('interface.bridge.bridge');
        $module
            ->setModuleTemplate('test')
            ->setVersionAdded('1.0.0');
        $resource = $this->createResource('interface.bridge.bridge');
        $event = new ModuleEvent($module, $resource);

        $listener = new DocumentationConfig();
        $listener->onPreCompile($event);

        $config = $event->getConfig();

        $this->assertTrue(isset($config['documentation']));
    }

    private function createModule($name)
    {
        $name = str_replace('.', '/', $name);
        $file = __DIR__."/../../../Fixtures/config/compiled/ansible/{$name}.yaml";
        $config = Yaml::parseFile($file);

        $module = new ModuleStructure();
        $module->fromArray($config);

        return $module;
    }

    private function createResource($name)
    {
        $name = str_replace('.', '/', $name);
        $file = __DIR__."/../../../Fixtures/config/compiled/resource/{$name}.yaml";
        $config = Yaml::parseFile($file);

        $resource = new ResourceStructure();
        $resource->fromArray($config);

        return $resource;
    }
}
