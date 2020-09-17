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

namespace RouterOS\Generator\Command;

use RouterOS\Generator\Event\BuildEvent;
use RouterOS\Generator\Listener\ProcessEventSubscriber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BuildCommand extends Command
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ProcessEventSubscriber
     */
    private $processListener;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ProcessEventSubscriber $processListener
    ) {
        parent::__construct('build');
        $this->dispatcher = $dispatcher;
        $this->processListener = $processListener;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dispatcher = $this->dispatcher;
        $processListener = $this->processListener;

        $processListener->setOutput($output);
        $output->writeln('<info>Building RouterOS</info>');
        $output->writeln('');

        // start build
        $event = new BuildEvent($output);
        $dispatcher->dispatch($event, BuildEvent::PREPARE);
        $dispatcher->dispatch($event, BuildEvent::BUILD);

        return Command::SUCCESS;
    }
}
