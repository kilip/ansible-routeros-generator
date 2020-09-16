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

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BuildEvent extends Event
{
    public const PREPARE = 'build.prepare';
    public const BUILD = 'build';
    public const TEST_PREPARE = 'test.prepare';

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }
}
