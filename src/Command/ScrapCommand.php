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
use RouterOS\Generator\Processor\ScrapingProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapCommand extends Command
{
    protected static $defaultName = 'routeros:scrap';

    /**
     * @var ConsoleProcessEventSubscriber
     */
    private $consoleProcessEventSubscriber;

    /**
     * @var ScrapingProcessor
     */
    private $scrapingProcessor;

    public function __construct(
        ConsoleProcessEventSubscriber $consoleProcessEventSubscriber,
        ScrapingProcessor $scrapingProcessor
    ) {
        $this->consoleProcessEventSubscriber = $consoleProcessEventSubscriber;
        $this->scrapingProcessor = $scrapingProcessor;
        parent::__construct(static::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleProcessEventSubscriber = $this->consoleProcessEventSubscriber;
        $scrapingProcessor = $this->scrapingProcessor;
        $consoleProcessEventSubscriber->setOutput($output);
        $scrapingProcessor->process();

        if ($consoleProcessEventSubscriber->hasException()) {
            $consoleProcessEventSubscriber->renderException();

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
