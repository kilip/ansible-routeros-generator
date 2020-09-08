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

namespace RouterOS\Contracts;

use Doctrine\Persistence\ObjectRepository;
use RouterOS\Model\SubMenu;

interface SubMenuManagerInterface
{
    /**
     * @return SubMenu
     */
    public function findOrCreate(string $name): SubMenu;

    public function update(SubMenu $object);

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
