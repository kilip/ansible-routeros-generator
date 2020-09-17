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
use RouterOS\Generator\Util\Text;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestConfig implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ModuleEvent::PRE_COMPILE => 'onPreCompile',
        ];
    }

    public function onPreCompile(ModuleEvent $event)
    {
        $module = $event->getModule();
        $resource = $event->getResource();

        $event->addConfig([
            'tests' => [
                'facts' => $this->generateFacts($module, $resource),
                'unit' => $this->generateUnitTests($module, $resource),
            ],
        ]);
    }

    private function generateFacts(ModuleStructure $module, ResourceStructure $resource)
    {
        $fixtures = $module->getFixtures();
        $fixtureContents = Text::toRouterosExport($resource, $fixtures);

        return [
            'name' => $resource->getName(),
            'fixture_contents' => $fixtureContents,
            'fixtures' => $fixtures,
        ];
    }

    private function generateUnitTests(ModuleStructure $module, ResourceStructure $resource)
    {
        $fixtures = $module->getFixtures();
        $fixtureContents = Text::toRouterosExport($resource, $fixtures);
        $examples = $module->getExamples();
        $type = $resource->getType();
        $tests = [];

        foreach ($examples as $example) {
            $cmds = Text::toRouterosCommands($resource, $example['verify']);
            $argumentSpec = $example['argument_spec'];
            if ('config' == $type) {
                foreach ($argumentSpec['config'] as $index => $values) {
                    ksort($values);
                    $argumentSpec['config'][$index] = $values;
                }
            } else {
                ksort($argumentSpec['config']);
            }

            $tests[] = [
                'commands' => $cmds,
                'argument_spec' => $argumentSpec,
            ];
        }

        return [
            // @TODO: use ansible module prefix
            'module_name' => 'ros_'.$module->getName(),
            'fixture_contents' => $fixtureContents,
            'tests' => $tests,
        ];
    }
}
