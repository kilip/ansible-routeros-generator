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

class Meta
{
    public const OPTIONS_NON_COMMENTED_SUBMENU = 'non-commented-submenu';
    public const OPTIONS_NON_DISABILITY_SUBMENU = 'non-disability-submenu';

    use ArrayStructureTrait;

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
     * Generator properties.
     *
     * @var array
     */
    private $generator = [];

    /**
     * @var array
     */
    private $keys = ['name'];

    /**
     * @var string
     */
    private $type = 'config';

    /**
     * @var string|null
     */
    private $configFile;

    /**
     * @var array
     */
    private $options = [];

    private $validOptions = [];

    /**
     * @var array
     */
    private $propertiesOverride = [];

    public function __construct()
    {
        $this->validOptions = [
            static::OPTIONS_NON_COMMENTED_SUBMENU,
            static::OPTIONS_NON_DISABILITY_SUBMENU,
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
     * @return string
     */
    public function getPackage(): string
    {
        return $this->package;
    }

    /**
     * @param string $package
     */
    public function setPackage(string $package): void
    {
        $this->package = $package;
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
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
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
     */
    public function setGenerator(array $generator): void
    {
        $this->generator = $generator;
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
     */
    public function setKeys(array $keys): void
    {
        $this->keys = $keys;
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
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getConfigFile(): ?string
    {
        return $this->configFile;
    }

    /**
     * @param string $configFile
     */
    public function setConfigFile(string $configFile): void
    {
        $this->configFile = $configFile;
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
     * @return array
     */
    public function getValidOptions(): array
    {
        return $this->validOptions;
    }

    /**
     * @param array $validOptions
     */
    public function setValidOptions(array $validOptions): void
    {
        $this->validOptions = $validOptions;
    }

    /**
     * @return array
     */
    public function getPropertiesOverride(): array
    {
        return $this->propertiesOverride;
    }

    /**
     * @param array $propertiesOverride
     */
    public function setPropertiesOverride(array $propertiesOverride): void
    {
        $this->propertiesOverride = $propertiesOverride;
    }
}
