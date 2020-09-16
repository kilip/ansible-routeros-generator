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
use RouterOS\Generator\Structure\ResourceProperty;
use RouterOS\Generator\Structure\ResourceStructure;

trait ListenerTrait
{
    protected function normalizeProperties(ModuleStructure $module, ResourceStructure $resource, $options = null)
    {
        $ignores = $module->getIgnores();
        $keys = $resource->getKeys();
        $props = [];
        foreach ($resource->getProperties() as $name => $property) {
            if(
                in_array($name, $ignores)
                || in_array($property->getOriginalName(), $ignores)
            ){
                continue;
            }

            $type = $this->translateType($property->getType());
            $prop = [
                'type' => $type,
            ];

            $choices = $property->getChoices();
            if (!empty($choices)) {
                if ($choices == ['yes', 'no'] || $choices == ['no', 'yes']) {
                    $prop['type'] = 'bool';
                } else {
                    $choices = $property->getChoices();
                    foreach($choices as $index => $choice){
                        if($type == 'int'){
                            $choices[$index] = intval($choice);
                        }
                    }
                    $prop['choices'] = $choices;
                }

            }

            if (
                null !== $property->getDefault()
                && !\in_array($name, $keys, true)
            ) {
                $default = $property->getDefault();
                if ('bool' == $prop['type']) {
                    $prop['default'] = 'yes' == $default ? 'True' : 'False';
                } elseif ('none' !== $default) {
                    $prop['default'] = $default;
                }
            }

            if (\in_array($name, $keys, true)) {
                $prop['required'] = 'True';
            }

            if (
                !ModuleEvent::PROPERTY_NO_DESCRIPTION & $options
                && null !== $property->getDescription()
            ) {
                $prop['description'] = $property->getDescription();
            }

            $props[$name] = $prop;
        }

        return $props;
    }

    protected function translateType($type)
    {
        $map = [
            ResourceProperty::TYPE_STRING => 'str',
            ResourceProperty::TYPE_BOOL => 'bool',
            ResourceProperty::TYPE_LIST => 'list',
            ResourceProperty::TYPE_INTEGER => 'int',
        ];

        return $map[$type] ?? 'str';
    }
}
