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

use RouterOS\Generator\Provider\Ansible\Event\BuildEvent;
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

    public function __construct(
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct('ansible:build');

        $this->dispatcher = $dispatcher;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dispatcher = $this->dispatcher;
        $event = new BuildEvent($output);

        $output->writeln('<info>Preparing Build</info>');
        $dispatcher->dispatch($event, BuildEvent::PREPARE);

        return Command::SUCCESS;
    }
}
