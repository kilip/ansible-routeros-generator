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

namespace RouterOS\Generator\Util;

class ProcessItem
{
    /**
     * @var array
     */
    private $commands;

    /**
     * @var string
     */
    private $workingDir;

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var callable|null
     */
    private $afterProcess;

    public function __construct(
        array $commands,
        string $workingDir,
        string $message = null,
        callable $afterProcess = null
    ) {
        $this->commands = $commands;
        $this->workingDir = $workingDir;
        $this->message = $message;
        $this->afterProcess = $afterProcess;
    }

    /**
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @return string
     */
    public function getWorkingDir(): string
    {
        return $this->workingDir;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return callable|null
     */
    public function getAfterProcess(): ?callable
    {
        return $this->afterProcess;
    }
}
