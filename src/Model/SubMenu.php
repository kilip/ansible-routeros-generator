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

namespace RouterOS\Generator\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SubMenu.
 *
 * @ORM\Entity
 * @ORM\Table(name="td_sub_menu")
 */
class SubMenu
{
    public const OPTIONS_NON_COMMENTED_SUBMENU = 'non-commented-submenu';
    public const OPTIONS_NON_DISABILITY_SUBMENU = 'non-disability-submenu';

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $package;

    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @var string
     */
    private $command;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Property",
     *     mappedBy="subMenu",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var Collection|Property[]
     */
    private $properties;

    /**
     * Generator properties.
     *
     * @ORM\Column(type="array")
     *
     * @var array
     */
    private $generator = [];

    /**
     * @ORM\Column(type="array")
     *
     * @var array
     */
    private $keys = ['name'];

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $type = 'config';

    /**
     * @ORM\Column(type="array")
     *
     * @var array
     */
    private $options = [];

    private $validOptions = [];

    public function __construct()
    {
        $this->properties = new ArrayCollection();
        $this->validOptions = [
            static::OPTIONS_NON_COMMENTED_SUBMENU,
            static::OPTIONS_NON_DISABILITY_SUBMENU,
        ];
    }

    /**
     * @return Collection|Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param Collection|Property[] $properties
     */
    public function setProperties($properties): void
    {
        $this->properties = $properties;
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
     * @param string $name
     *
     * @return Property
     */
    public function getProperty(string $name)
    {
        if (null === $this->properties) {
            $this->properties = new ArrayCollection();
        }
        foreach ($this->properties->toArray() as $property) {
            if ($property->getName() == $name) {
                return $property;
            }
        }
        $property = new Property();
        $property->setName($name);
        $property->setSubMenu($this);
        $this->properties->add($property);

        return $property;
    }

    public function addProperty(Property $property)
    {
        $property->setSubMenu($this);
        if (!$this->hasProperty($property->getName())) {
            $this->properties->add($property);
        }

        return $this;
    }

    public function hasProperty($name)
    {
        foreach ($this->properties as $property) {
            if ($property->getName() == $name) {
                return true;
            }
        }

        return false;
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
    public function getGenerator(): array
    {
        return $this->generator;
    }

    /**
     * @param array $generator
     *
     * @return static
     */
    public function setGenerator(array $generator)
    {
        $this->generator = $generator;

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
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
