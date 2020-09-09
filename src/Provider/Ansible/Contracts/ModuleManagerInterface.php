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

namespace RouterOS\Generator\Provider\Ansible\Contracts;

use RouterOS\Generator\Provider\Ansible\Model\Module;

interface ModuleManagerInterface
{
    /**
     * @return Module
     */
    public function create(): Module;

    /**
     * @param string $name
     *
     * @return Module
     */
    public function findOrCreate(string $name): Module;

    /**
     * @param string $name
     *
     * @return Module|null
     */
    public function findByName(string $name);

    /**
     * @return array
     */
    public function getModuleList(): array;

    /**
     * Update module.
     *
     * @param Module $module
     * @param bool   $andFlush
     */
    public function update(Module $module, $andFlush = true): void;
}
