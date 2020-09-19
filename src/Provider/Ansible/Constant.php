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

namespace RouterOS\Generator\Provider\Ansible;

class Constant
{
    public const TEST_UNITS = 'ansible-test.unit';
    public const TEST_FACTS = 'ansible-test.facts';

    /**
     * @var string
     */
    private $modulesDir;

    /**
     * @var string
     */
    private $resourcesDir;

    /**
     * @var string
     */
    private $moduleTestDir;

    /**
     * @var string
     */
    private $moduleTestFixtureDir;

    /**
     * @var string
     */
    private $moduleTestFactsDir;

    /**
     * @var string
     */
    private $moduleTestFactsFixtureDir;

    /**
     * @var string
     */
    private $moduleIntegrationDir;

    /**
     * @var string
     */
    private $modulePrefix;

    /**
     * @var string
     */
    private $moduleCompletePrefix;

    /**
     * @var string
     */
    private $targetDir;
    /**
     * @var string
     */
    private $compiledDir;

    /**
     * @var string
     */
    private $venvDir;

    /**
     * @var string
     */
    private $lockDir;

    public function __construct(
        string $modulePrefix,
        string $moduleCompletePrefix,
        string $targetDir,
        string $compiledDir
    ) {
        $this->modulePrefix = $modulePrefix;
        $this->moduleCompletePrefix = $moduleCompletePrefix;
        $this->targetDir = $targetDir;
        $this->compiledDir = $compiledDir;
        $this->modulesDir = "{$targetDir}/plugins/modules";
        $this->resourcesDir = "{$targetDir}/plugins/module_utils/resources";
        $this->moduleTestDir = "{$targetDir}/tests/unit/modules/generator/modules";
        $this->moduleTestFixtureDir = "{$this->moduleTestDir}/fixtures";
        $this->moduleTestFactsDir = "{$targetDir}/tests/unit/modules/generator/facts";
        $this->moduleTestFactsFixtureDir = "{$this->moduleTestFactsDir}/fixtures";
        $this->moduleIntegrationDir = "{$targetDir}/tests/integration/targets";
        $this->venvDir = "{$targetDir}/.venv";
        $this->lockDir = "{$this->venvDir}/routeros";
    }

    /**
     * @return string
     */
    public function getCompiledDir(): string
    {
        return $this->compiledDir;
    }

    /**
     * @return string
     */
    public function getVenvDir(): string
    {
        return $this->venvDir;
    }

    /**
     * @return string
     */
    public function getLockDir(): string
    {
        return $this->lockDir;
    }

    /**
     * @return string
     */
    public function getModuleIntegrationDir(): string
    {
        return $this->moduleIntegrationDir;
    }

    /**
     * @return string
     */
    public function getModulesDir(): string
    {
        return $this->modulesDir;
    }

    /**
     * @return string
     */
    public function getResourcesDir(): string
    {
        return $this->resourcesDir;
    }

    /**
     * @return string
     */
    public function getModuleTestDir(): string
    {
        return $this->moduleTestDir;
    }

    /**
     * @return string
     */
    public function getModuleTestFixtureDir(): string
    {
        return $this->moduleTestFixtureDir;
    }

    /**
     * @return string
     */
    public function getModuleTestFactsDir(): string
    {
        return $this->moduleTestFactsDir;
    }

    /**
     * @return string
     */
    public function getModuleTestFactsFixtureDir(): string
    {
        return $this->moduleTestFactsFixtureDir;
    }

    /**
     * @return string
     */
    public function getModulePrefix(): string
    {
        return $this->modulePrefix;
    }

    /**
     * @return string
     */
    public function getModuleCompletePrefix(): string
    {
        return $this->moduleCompletePrefix;
    }

    /**
     * @return string
     */
    public function getTargetDir(): string
    {
        return $this->targetDir;
    }
}
