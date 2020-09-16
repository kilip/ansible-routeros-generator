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

class ModuleExampleListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ModuleEvent::PRE_COMPILE => 'onPreCompile',
        ];
    }

    public function onPreCompile(ModuleEvent $event)
    {
        $examples = [];
        $module = $event->getModule();
        $resource = $event->getResource();

        foreach ($module->getExamples() as $example) {
            $examples[] = $this->generateExample($resource, $module, $example);
        }
        $config['examples'] = $examples;

        $event->addConfig($config);
    }

    private function generateExample(ResourceStructure $resource, ModuleStructure $module, array $config)
    {
        $before = Text::arrayToRouteros($resource, $module->getFixtures());
        $after = $this->generateAfter($resource, $config['verify']);

        $example = [];
        $example['title'] = $config['title'];
        $example['name'] = $config['name'];
        $example['argument_spec'] = $config['argument_spec'];
        $example['before'] = $before;
        $example['after'] = $after;
        $example['commands'] = $this->generateCommands($resource, $config['verify']);

        return $example;
    }

    private function generateAfter(ResourceStructure $resource, $verify)
    {
        $after = Text::toRouterosExport($resource, $verify);

        return trim($after);
    }

    private function generateCommands(ResourceStructure $resource, $verify)
    {
        $keys = $resource->getKeys();
        $command = $resource->getCommand();
        $cmds = [];

        foreach ($verify as $config) {
            $action = $config['action'];
            if ('script' == $action) {
                $cmd = [$config['script']];
            } else {
                $cmd = [$command, $action];
                ksort($config['values']);
                if (
                    !\in_array($action, ['add', 'remove'], true)
                    && 'config' == $resource->getType()
                ) {
                    $cmd[] = $this->generateFindCommand($resource, $config['values']);
                }
                foreach ($config['values'] as $name => $value) {
                    if (!\in_array($name, $keys, true) || \in_array($action, ['add', 'remove'], true)) {
                        $rosName = Text::getOriginalName($resource, $name);
                        $cmd[] = "{$rosName}=".Text::quoteRouterOSValue($value);
                    }
                }
            }

            $cmds[] = implode(' ', $cmd);
        }

        return $cmds;
    }

    private function generateFindCommand(ResourceStructure $resource, $values)
    {
        $keys = $resource->getKeys();
        $cmds = [];
        foreach ($values as $name => $value) {
            if (\in_array($name, $keys, true)) {
                $value = Text::quoteRouterOSValue($value);
                $cmds[] = "{$name}={$value}";
            }
        }

        return '[ find '.implode(' ', $cmds).' ]';
    }
}
