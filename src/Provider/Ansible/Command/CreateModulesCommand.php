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

namespace RouterOS\Generator\Provider\Ansible\Command;

use Symfony\Component\Console\Command\Command;

class CreateModulesCommand extends Command
{
    protected static $defaultName = 'ansible:create-modules';

    public function __construct(
    ) {
        parent::__construct(static::$defaultName);
    }
}
