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

namespace RouterOS\Generator\Provider\Ansible\Model;

use RouterOS\Generator\Model\Property;

class Documentation implements \ArrayAccess
{
    /**
     * @var array
     */
    private $doc;

    public function __construct(Module $module)
    {
        $this->fromModule($module);
    }

    public function offsetExists($offset)
    {
        return isset($this->doc[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->doc[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->doc[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->doc[$offset]);
    }

    private function fromModule(Module $module)
    {
        $keys = [
            'author',
            'short_description',
            'description',
            'version_added',
            'author',
            'notes',
        ];
        $config = $module->getConfig();
        $subMenu = $module->getSubMenu();

        $doc = [];
        foreach ($config as $name => $value) {
            if (\in_array($name, $keys, true)) {
                $doc[$name] = $value;
            }
        }

        $subOptions = [];
        if(!is_null($subMenu)){
            foreach ($subMenu->getProperties() as $property) {
                $option = [];
                $name = $property->getName();
                $type = $this->translateType($property->getType());
                $option['type'] = $type;

                if (!empty($property->getChoices())) {
                    $option['choices'] = $property->getChoices();
                }

                if (true === $property->isRequired()) {
                    $option['required'] = 'True';
                } elseif (null !== $property->getDefaultValue()) {
                    $option['default'] = $property->getDefaultValue();
                }

                if ('list' == $type) {
                    $option['elements'] = 'str';
                }

                $subOptions[$name] = $option;
            }
        }

        $doc['suboptions'] = $subOptions;

        $this->doc = $doc;
    }

    private function translateType($type)
    {
        $map = [
            Property::TYPE_STRING => 'str',
            Property::TYPE_INTEGER => 'int',
            Property::TYPE_BOOL => 'bool',
            Property::TYPE_LIST => 'list',
        ];

        return $map[$type];
    }
}
