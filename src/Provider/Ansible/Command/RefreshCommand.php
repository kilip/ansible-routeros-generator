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
use RouterOS\Generator\Provider\Ansible\Processor\ModuleRefreshProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshCommand extends Command
{
    protected static $defaultName = 'ansible:refresh';
    /**
     * @var ProcessEventSubscriber
     */
    private $processListener;

    /**
     * @var ModuleRefreshProcessor
     */
    private $processor;

    public function __construct(
        ProcessEventSubscriber $processListener,
        ModuleRefreshProcessor $processor
    ) {
        parent::__construct(static::$defaultName);

        $this->processListener = $processListener;
        $this->processor = $processor;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processor = $this->processor;
        $consoleProcessSubscriber = $this->processListener;

        $consoleProcessSubscriber->setOutput($output);
        $processor->process();

        return Command::SUCCESS;
    }
}
