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

use RouterOS\Generator\Listener\ConsoleProcessEventSubscriber;
use RouterOS\Generator\Provider\Ansible\ConfigLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexModuleCommand extends Command
{
    protected static $defaultName = 'ansible:reindex-module';
    /**
     * @var ConfigLoader
     */
    private $configLoader;
    /**
     * @var ConsoleProcessEventSubscriber
     */
    private $consoleProcessSubscriber;

    public function __construct(
        ConfigLoader $configLoader,
        ConsoleProcessEventSubscriber $consoleProcessSubscriber
    ) {
        parent::__construct(static::$defaultName);
        $this->configLoader = $configLoader;
        $this->consoleProcessSubscriber = $consoleProcessSubscriber;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->consoleProcessSubscriber->setOutput($output);
        $loader = $this->configLoader;
        $loader->refresh();

        return Command::SUCCESS;
    }
}
