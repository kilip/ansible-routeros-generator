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

namespace RouterOS\Generator\Structure;

use RouterOS\Generator\Util\ArrayStructureTrait;

/**
 * Resource Class.
 */
class ResourceStructure
{
    use ArrayStructureTrait {
        toArray as traitToArray;
        fromArray as traitFromArray;
    }

    public const OPTIONS_NON_COMMENTED = 'non-commented-submenu';
    public const OPTIONS_NON_DISABILITY = 'non-disability-submenu';

    /**
     * @var string
     */
    private $package;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $command;

    /**
     * @var ResourceProperty[]
     */
    private $properties = [];

    /**
     * @var array
     */
    private $keys = ['name'];

    /**
     * @var string
     */
    private $type = 'config';

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var \Exception[]
     */
    private $exceptions = [];

    public function __construct()
    {
        $this->ignoredExports = ['property'];
        $this->ignoredImports = ['properties'];
    }

    public function addProperty(ResourceProperty $property)
    {
        $name = $property->getName();
        $this->properties[$name] = $property;
        ksort($this->properties);
    }

    public function hasProperty($name)
    {
        return isset($this->properties[$name]);
    }

    public function getProperty($name, $createIfNotExists = false)
    {
        if (!$this->hasProperty($name)) {
            if ($createIfNotExists) {
                $property = new ResourceProperty();
                $property->setName($name);
                $this->addProperty($property);
            } else {
                throw new \InvalidArgumentException("Resource {$this->name} doesn't have property {$name}.");
            }
        }

        return $this->properties[$name];
    }

    /**
     * @return ResourceProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param ResourceProperty[] $properties
     *
     * @return static
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    public function fromMeta(Meta $meta)
    {
        $data = $meta->toArray();
        $this->fromArray($data);
        $this->importProperties($meta->getPropertiesOverride());
    }

    public function fromArray($config)
    {
        $this->traitFromArray($config);

        if (isset($config['properties'])) {
            $this->importProperties($config['properties']);
        }
    }

    private function importProperties(array $properties)
    {
        foreach ($properties as $name => $override) {
            $property = $this->getProperty($name, true);
            $property->fromArray($override);
            $property->setName($name);
            $this->addProperty($property);
        }
    }

    public function toArray()
    {
        $data = $this->traitToArray();

        unset($data['exceptions']);
        foreach ($this->properties as $name => $property) {
            if ($property instanceof ResourceProperty) {
                $property = $property->toArray();
            }
            $data['properties'][$name] = $property;
        }

        return $data;
    }

    public function hasException()
    {
        return \count($this->exceptions) > 0;
    }

    public function addException(\Exception $exception)
    {
        $this->exceptions[] = $exception;
    }

    public function getExceptions()
    {
        return $this->exceptions;
    }

    public static function getValidOptions()
    {
        return  [
            static::OPTIONS_NON_COMMENTED,
            static::OPTIONS_NON_DISABILITY,
        ];
    }

    /**
     * @param mixed $name
     *
     * @return bool true If object has option
     */
    public function hasOption($name)
    {
        return \in_array($name, $this->options, true);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getPackage(): string
    {
        return $this->package;
    }

    /**
     * @param string $package
     *
     * @return static
     */
    public function setPackage(string $package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     *
     * @return static
     */
    public function setCommand(string $command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * @param array $keys
     *
     * @return static
     */
    public function setKeys(array $keys)
    {
        $this->keys = $keys;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return static
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }
}
