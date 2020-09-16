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

use RouterOS\Generator\Structure\Meta;

interface MetaManagerInterface
{
    /**
     * @return array
     */
    public function getList(): array;

    /**
     * @param string $name
     *
     * @return Meta
     */
    public function getMeta($name): Meta;
}
