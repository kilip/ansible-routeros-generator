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

namespace RouterOS\Generator\Provider\Ansible\Event;

use RouterOS\Generator\Provider\Ansible\Structure\ModuleStructure;
use RouterOS\Generator\Structure\ResourceStructure;
use RouterOS\Generator\Util\Text;

class ModuleEvent
{
    public const PRE_COMPILE = 'pre.compile';

    public const PROPERTY_NO_DESCRIPTION = 2;

    /**
     * @var ModuleStructure
     */
    private $module;

    /**
     * @var ResourceStructure
     */
    private $resource;

    private $config = [];

    public function __construct(
        ModuleStructure $module,
        ResourceStructure $resource
    ) {
        $this->module = $module;
        $this->resource = $resource;

        $exportCommand = $resource->getCommand().' export';
        $resourceClassName = Text::classify($module->getName()).'Resource';
        $this->config = [
            'name' => $module->getName(),
            'package' => $module->getPackage(),
            'type' => $resource->getType(),
            'template' => $module->getModuleTemplate(),
            'module_name' => $module->getModuleName(),
            'export_command' => $exportCommand,
            'resource_class_name' => $resourceClassName,
        ];
    }

    /**
     * @return ModuleStructure|null
     */
    public function getModule(): ?ModuleStructure
    {
        return $this->module;
    }

    /**
     * @return ResourceStructure
     */
    public function getResource(): ResourceStructure
    {
        return $this->resource;
    }

    public function addConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
