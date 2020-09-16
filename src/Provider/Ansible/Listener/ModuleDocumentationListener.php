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

namespace RouterOS\Generator\Provider\Ansible\Listener;

use RouterOS\Generator\Provider\Ansible\Event\ModuleEvent;
use RouterOS\Generator\Provider\Ansible\Structure\ModuleStructure;
use RouterOS\Generator\Structure\ResourceStructure;
use RouterOS\Generator\Util\Text;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModuleDocumentationListener implements EventSubscriberInterface
{
    use ListenerTrait;

    public static function getSubscribedEvents()
    {
        return [
            ModuleEvent::PRE_COMPILE => 'onPreCompile',
        ];
    }

    public function onPreCompile(ModuleEvent $event)
    {
        $module = $event->getModule();
        $documentation = $module->toArray([
            'fixtures',
            'examples',
            'states',
            'default_state',
            'package',
            'module_name',
            'module_template',
            'name',
        ]);

        $orders = [
            'options',
            'description',
            'short_description',
            'author',
            'version_added',
            'module',
        ];
        $documentation['options'] = $this->generateOptions($event);
        $documentation['module'] = 'ros_'.$module->getName();
        $documentation = Text::arrayKeySort($orders, $documentation);

        $event->addConfig([
            'documentation' => $documentation,
        ]);
    }

    private function generateOptions(ModuleEvent $event)
    {
        $options = [];

        $module = $event->getModule();
        $resource = $event->getResource();
        $states = $module->getStates();

        $options['state'] = [
            'choices' => $states,
            'default' => $module->getDefaultState(),
            'description' => 'Set state for this module',
        ];

        $options['config'] = $this->generateConfig($module, $resource);

        return $options;
    }

    private function generateConfig(ModuleStructure $module, ResourceStructure $resource)
    {
        $config = [];
        $type = 'config' == $resource->getType() ? 'list' : 'dict';
        $config['type'] = $type;
        if ('config' == $resource->getType()) {
            $config['elements'] = 'dict';
        }

        $config['suboptions'] = $this->normalizeProperties($module, $resource);

        return $config;
    }
}
