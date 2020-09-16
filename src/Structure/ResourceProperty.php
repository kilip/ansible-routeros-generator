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
 * ResourceProperty Class.
 */
class ResourceProperty
{
    use ArrayStructureTrait;

    public const TYPE_INTEGER = 'integer';
    public const TYPE_STRING = 'string';
    public const TYPE_BOOL = 'bool';
    public const TYPE_LIST = 'list';

    public const OPTION_IGNORE_CHOICES = 'ignore-choices';
    public const OPTION_IGNORE_DEFAULT = 'ignore-default';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var bool
     */
    private $required = false;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var array
     */
    private $choices = [];

    /**
     * @var string|null
     */
    private $choiceType;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var string|null
     */
    private $originalName;

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
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     *
     * @return static
     */
    public function setDefault($default)
    {
        if (\is_string($default)) {
            $data = @unserialize($default);
            if (false !== $data) {
                $default = $data;
            }
        }

        $this->default = $default;

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
        foreach ($choices as $index => $choice) {
            if (self::TYPE_INTEGER == $this->getType()) {
                $choices[$index] = (int) $choice;
            }
        }
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
     * @return string
     */
    public function getOriginalName(): string
    {
        if (null === $this->originalName) {
            $this->originalName = strtr($this->name, [
                '_' => '-',
            ]);
        }

        return $this->originalName;
    }

    /**
     * @param string $originalName
     *
     * @return static
     */
    public function setOriginalName(string $originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }
}
