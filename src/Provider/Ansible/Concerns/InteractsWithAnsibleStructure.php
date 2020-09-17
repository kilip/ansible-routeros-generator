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

namespace RouterOS\Generator\Provider\Ansible\Concerns;

use RouterOS\Generator\Concerns\InteractsWithStructure;
use RouterOS\Generator\Provider\Ansible\Structure\ModuleStructure;

trait InteractsWithAnsibleStructure
{
    use InteractsWithStructure;

    protected function createModule($name)
    {
        $configDir = $this->getParameter('ansible.config_dir');
        $config = $this->getConfig($configDir, $name);

        $module = new ModuleStructure();
        $module->fromArray($config);

        return $module;
    }

    protected function getModuleConfig($namespace)
    {
        $configDir = $this->getParameter('ansible.compiled_dir');

        return $this->getConfig($configDir, $namespace);
    }
}
