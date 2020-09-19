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
use RouterOS\Generator\Provider\Ansible\Event\AnsibleTestEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AnsibleTestCommand extends Command
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ProcessEventSubscriber
     */
    private $processEventSubscriber;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ProcessEventSubscriber $processEventSubscriber
    ) {
        parent::__construct('ansible-test');
        $this->dispatcher = $dispatcher;
        $this->processEventSubscriber = $processEventSubscriber;
    }

    protected function configure()
    {
        $this->addOption(
            'type',
            't',
            InputOption::VALUE_REQUIRED,
            'Filter test type',
            'all'
        );

        $this->addArgument(
            'module',
            InputArgument::OPTIONAL,
            'Running tests for module only.',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dispatcher = $this->dispatcher;
        $processEventSubscriber = $this->processEventSubscriber;

        $processEventSubscriber->setOutput($output);

        $type = $input->getOption('type');
        $module = $input->getArgument('module');

        $event = new AnsibleTestEvent($this->getApplication(), $input, $output, $module);

        if ('unit' == $type) {
            $output->writeln('<info>Running Modules Unit Test</info>');
            $dispatcher->dispatch($event, AnsibleTestEvent::UNIT);
        } elseif ('facts' == $type) {
            $output->writeln('<info>Running Facts Tests</info>');
            $dispatcher->dispatch($event, AnsibleTestEvent::UNIT);
        } elseif ('all' == $type) {
            $output->writeln('<info>Running Ansible Tests</info>');
            $dispatcher->dispatch($event, AnsibleTestEvent::ALL);
        } else {
            throw new \InvalidArgumentException('Test type invalid. Please choose unit,facts, or integration');
        }

        $return = Command::FAILURE;
        if (!$processEventSubscriber->hasException()) {
            return Command::SUCCESS;
        }

        return $return;
    }
}
