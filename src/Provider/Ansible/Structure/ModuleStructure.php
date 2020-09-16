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

namespace RouterOS\Generator\Provider\Ansible\Structure;

use RouterOS\Generator\Util\ArrayStructureTrait;

class ModuleStructure
{
    use ArrayStructureTrait;

    private $name;

    private $package;

    private $author;

    private $moduleName;

    private $shortDescription;

    /**
     * @var array
     */
    private $description = [];

    private $states;

    private $defaultState;

    /**
     * @var array
     */
    private $fixtures = [];

    /**
     * @var array
     */
    private $examples = [];

    /**
     * @var string
     */
    private $moduleTemplate = '@ansible/module/module.py.twig';

    /**
     * @var string
     */
    private $versionAdded;

    /**
     * @var array
     */
    private $ignores = [];

    /**
     * @var array
     */
    private $supports = [];

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param mixed $package
     *
     * @return static
     */
    public function setPackage($package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     *
     * @return static
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @param mixed $moduleName
     *
     * @return static
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param mixed $shortDescription
     *
     * @return static
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @return array
     */
    public function getDescription(): array
    {
        return $this->description;
    }

    /**
     * @param array $description
     *
     * @return static
     */
    public function setDescription(array $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @param mixed $states
     *
     * @return static
     */
    public function setStates($states)
    {
        $this->states = $states;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultState()
    {
        return $this->defaultState;
    }

    /**
     * @param mixed $defaultState
     *
     * @return static
     */
    public function setDefaultState($defaultState)
    {
        $this->defaultState = $defaultState;

        return $this;
    }

    /**
     * @return array
     */
    public function getFixtures(): array
    {
        return $this->fixtures;
    }

    /**
     * @param array $fixtures
     *
     * @return static
     */
    public function setFixtures(array $fixtures)
    {
        $this->fixtures = $fixtures;

        return $this;
    }

    /**
     * @return array
     */
    public function getExamples(): array
    {
        return $this->examples;
    }

    /**
     * @param array $examples
     *
     * @return static
     */
    public function setExamples(array $examples)
    {
        $this->examples = $examples;

        return $this;
    }

    /**
     * @return string
     */
    public function getModuleTemplate(): string
    {
        return $this->moduleTemplate;
    }

    /**
     * @param string $moduleTemplate
     *
     * @return static
     */
    public function setModuleTemplate(string $moduleTemplate)
    {
        $this->moduleTemplate = $moduleTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersionAdded(): string
    {
        return $this->versionAdded;
    }

    /**
     * @param string $versionAdded
     *
     * @return static
     */
    public function setVersionAdded(string $versionAdded)
    {
        $this->versionAdded = $versionAdded;

        return $this;
    }

    /**
     * @param array $ignores
     */
    public function setIgnores(array $ignores)
    {
        $this->ignores = $ignores;

        return $this;
    }

    public function getIgnores(): array
    {
        return $this->ignores;
    }

    public function setSupports(array $supports)
    {
        $this->supports = $supports;
        return $this;
    }

    public function getSupports()
    {
        return $this->supports;
    }
}
