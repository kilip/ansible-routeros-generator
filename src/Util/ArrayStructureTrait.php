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

namespace RouterOS\Generator\Util;

use Doctrine\Inflector\InflectorFactory;
use RouterOS\Generator\Structure\ResourceProperties;

trait ArrayStructureTrait
{
    protected $ignoredExports = [];

    protected $ignoredImports = [];

    public function fromArray(array $config)
    {
        $inflector = InflectorFactory::create()->build();
        foreach ($config as $name => $value) {
            if(in_array($name, $this->ignoredImports)){
                continue;
            }
            $setter = 'set'.ucfirst($inflector->camelize($name));
            if (method_exists($this, $setter)) {
                \call_user_func([$this, $setter], $value);
            }
        }
    }

    /**
     * @param array $filters
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    public function toArray(array $filters = [])
    {
        $r = new \ReflectionClass(static::class);
        $methods = $r->getMethods();
        $inflector = InflectorFactory::create()->build();
        $data = [];
        $filters = array_merge($this->ignoredExports, $filters);
        foreach ($methods as $method) {
            $name = $method->getName();
            $propName = strtr($name, [
                'is' => '',
                'get' => '',
            ]);
            $propName = $inflector->tableize($propName);

            if (
                $method->isStatic()
                || \in_array($propName, $filters, true)
            ) {
                continue;
            }

            if (
                0 === strpos($name, 'get')
                || 0 === strpos($name, 'is')
            ) {
                $getter = $name;
                $key = strtr($name, [
                    'get' => '',
                    'is' => '',
                ]);
                $key = $inflector->tableize($key);
                $value = \call_user_func([$this, $getter]);

                if ($value instanceof ResourceProperties) {
                    $value = $value->toArray();
                }
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
