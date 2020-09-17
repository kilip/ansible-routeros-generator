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

use RouterOS\Generator\Listener\ProcessEventSubscriber;
use RouterOS\Generator\Processor\ScrapingProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapCommand extends Command
{
    protected static $defaultName = 'routeros:scrap';

    /**
     * @var ProcessEventSubscriber
     */
    private $processListener;

    /**
     * @var ScrapingProcessor
     */
    private $scrapingProcessor;

    public function __construct(
        ProcessEventSubscriber $processListener,
        ScrapingProcessor $scrapingProcessor
    ) {
        $this->processListener = $processListener;
        $this->scrapingProcessor = $scrapingProcessor;
        parent::__construct(static::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processListener = $this->processListener;
        $scrapingProcessor = $this->scrapingProcessor;
        $processListener->setOutput($output);
        $scrapingProcessor->process();

        if ($processListener->hasException()) {
            $processListener->renderException();

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
