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

namespace RouterOS\Generator\Provider\Ansible\Config;

use RouterOS\Generator\Provider\Ansible\Event\ModuleEvent;
use RouterOS\Generator\Provider\Ansible\Structure\ModuleStructure;
use RouterOS\Generator\Structure\ResourceStructure;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceConfig implements EventSubscriberInterface
{
    use ConfigTrait;

    public static function getSubscribedEvents()
    {
        return [
            ModuleEvent::PRE_COMPILE => 'onPreCompile',
        ];
    }

    public function onPreCompile(ModuleEvent $event)
    {
        $resource = $event->getResource();
        $module = $event->getModule();
        $r = [
            'name' => $module->getName(),
            'package' => $module->getPackage(),
            'command' => $resource->getCommand(),
            'config_type' => $resource->getType(),
            'keys' => $resource->getKeys(),
            'filters' => [],
            'supports' => $module->getSupports(),
        ];

        $r['argument_spec'] = $this->generateArgumentSpec($module, $resource);
        $r['custom_props'] = $this->generateCustomProps($module, $resource);

        $config['resource'] = $r;
        $event->addConfig($config);
    }

    private function generateArgumentSpec(ModuleStructure $module, ResourceStructure $resource)
    {
        if ('config' == $resource->getType()) {
            $config['type'] = 'list';
            $config['elements'] = 'dict';
        } else {
            $config['type'] = 'dict';
        }
        $config['options'] = $this->normalizeProperties($module, $resource, ModuleEvent::PROPERTY_NO_DESCRIPTION);

        $states = [
            'type' => 'str',
            'choices' => $module->getStates(),
            'default' => $module->getDefaultState(),
        ];

        return [
            'state' => $states,
            'config' => $config,
        ];
    }

    private function generateCustomProps(ModuleStructure $module, ResourceStructure $resource)
    {
        $customProps = [];

        foreach ($resource->getProperties() as $property) {
            $name = $property->getName();
            $test = str_replace('_', '-', $name);
            $originalName = $property->getOriginalName();
            if ($test !== $originalName) {
                $customProps[$name]['original_name'] = $property->getOriginalName();
            }
        }

        return $customProps;
    }
}
