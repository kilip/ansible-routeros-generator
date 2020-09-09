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

namespace RouterOS\Generator\Contracts;

use Doctrine\Persistence\ObjectRepository;
use RouterOS\Generator\Model\SubMenu;

interface SubMenuManagerInterface
{
    /**
     * @return SubMenu
     */
    public function create(): SubMenu;

    /**
     * @param string $name
     *
     * @return object|SubMenu|null
     */
    public function findByName(string $name);

    /**
     * @param string $name
     *
     * @return object|SubMenu
     */
    public function findOrCreate(string $name);

    /**
     * @param SubMenu $object
     * @param bool    $andFlush
     */
    public function update(SubMenu $object, bool $andFlush = true): void;

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository;

    /**
     * Returns lists submenu with name and id
     * return array.
     */
    public function getSubMenuList(): array;
}
