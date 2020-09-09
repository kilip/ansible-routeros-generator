<?php


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
     * @return object|Module
     */
    public function findOrCreate(string $name): Module;

    /**
     * @param string $name
     * @return null|Module
     */
    public function findByName(string $name);

    /**
     * @return array
     */
    public function getModuleList(): array;

    /**
     * Update module
     *
     * @param Module $module
     * @param bool $andFlush
     */
    public function update(Module $module, $andFlush = true): void;
}