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

use RouterOS\Generator\Listener\ProcessEventSubscriber;
use RouterOS\Generator\Provider\Ansible\Processor\CompileProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompileCommand extends Command
{
    protected static $defaultName = 'ansible:compile';

    /**
     * @var ProcessEventSubscriber
     */
    private $consoleListener;

    /**
     * @var CompileProcessor
     */
    private $processor;

    public function __construct(
        ProcessEventSubscriber $consoleListener,
        CompileProcessor $processor
    ) {
        parent::__construct(static::$defaultName);
        $this->consoleListener = $consoleListener;
        $this->processor = $processor;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleListener = $this->consoleListener;
        $processor = $this->processor;

        $consoleListener->setOutput($output);
        $processor->process();

        return Command::SUCCESS;
    }
}
