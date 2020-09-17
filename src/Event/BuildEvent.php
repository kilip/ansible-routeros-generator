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

namespace RouterOS\Generator\Event;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BuildEvent extends Event
{
    public const PREPARE = 'build.prepare';
    public const BUILD = 'build';
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function log($message, array $context = [])
    {
        $message = $this->renderMessage($message, $context);
        $this->output->writeln($message);
    }

    public function renderMessage($message, array $context = [])
    {
        foreach ($context as $key => $value) {
            $text = "<info>{$value}</info>";
            $message = str_replace('{'.$key.'}', $text, $message);
        }

        return "<info>{$message}</info>";
    }
}
