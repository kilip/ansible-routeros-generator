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

use RouterOS\Generator\Listener\ConsoleProcessEventSubscriber;
use RouterOS\Generator\Processor\ReindexMetaProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MetaCommand extends Command
{
    protected static $defaultName = 'routeros:meta:reindex';
    /**
     * @var ReindexMetaProcessor
     */
    private $metaProcessor;
    /**
     * @var ConsoleProcessEventSubscriber
     */
    private $consoleProcessEventSubscriber;

    public function __construct(
        ConsoleProcessEventSubscriber $consoleProcessEventSubscriber,
        ReindexMetaProcessor $metaProcessor
    ) {
        $this->metaProcessor = $metaProcessor;
        $this->consoleProcessEventSubscriber = $consoleProcessEventSubscriber;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleEventSubscriber = $this->consoleProcessEventSubscriber;
        $metaProcessor = $this->metaProcessor;

        try {
            $consoleEventSubscriber->setOutput($output);
            $output->writeln('');
            $output->writeln('');
            $metaProcessor->process();
            $output->writeln('');
            $output->writeln('');

            return Command::SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        }
    }
}
