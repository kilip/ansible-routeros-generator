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

namespace RouterOS\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="td_property")
 */
class Property
{
    public const TYPE_INTEGER = 'integer';
    public const TYPE_STRING = 'string';
    public const TYPE_BOOL = 'bool';
    public const TYPE_LIST = 'list';

    public const OPTION_IGNORE_CHOICES = 'ignore-choices';
    public const OPTION_IGNORE_DEFAULT = 'ignore-default';

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue(strategy="UUID")
     *
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     *
     * @var string|null
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20)
     *
     * @var string|null
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $required = false;

    /**
     * @ORM\Column(type="array")
     *
     * @var array
     */
    private $defaultValue;

    /**
     * @ORM\Column(type="array", nullable=true)
     *
     * @var array
     */
    private $choices = [];

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $choiceType;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string|null
     */
    private $description;

    /**
     * @ORM\Column(type="array")
     *
     * @var array
     */
    private $options = [];

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @var string|null
     */
    private $originalName;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="RouterOS\Model\SubMenu",
     *     inversedBy="properties",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var SubMenu
     */
    private $subMenu;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return SubMenu
     */
    public function getSubMenu(): SubMenu
    {
        return $this->subMenu;
    }

    /**
     * @param SubMenu $subMenu
     */
    public function setSubMenu(SubMenu $subMenu): void
    {
        $this->subMenu = $subMenu;
    }

    public function hasOption($name)
    {
        return \in_array($name, $this->options, true);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return static
     */
    public function setName(?string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     *
     * @return static
     */
    public function setType(?string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return static
     */
    public function setRequired(bool $required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     *
     * @return static
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return array
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    /**
     * @param array $choices
     *
     * @return static
     */
    public function setChoices(array $choices)
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getChoiceType(): ?string
    {
        return $this->choiceType;
    }

    /**
     * @param string|null $choiceType
     *
     * @return static
     */
    public function setChoiceType(?string $choiceType)
    {
        $this->choiceType = $choiceType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return static
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
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
     *
     * @return static
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    /**
     * @param string|null $originalName
     *
     * @return static
     */
    public function setOriginalName(?string $originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }
}
