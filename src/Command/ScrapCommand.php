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

namespace RouterOS\Command;

use Psr\Log\LoggerInterface;
use RouterOS\Event\ProcessEvent;
use RouterOS\Scraper\DocumentationScraper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ScrapCommand extends Command
{
    protected static $defaultName = 'routeros:scrap';

    /**
     * @var DocumentationScraper
     */
    private $scraper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProgressBar
     */
    private $progressBar;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    private $progressBarStarted = false;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger,
        DocumentationScraper $scraper
    ) {
        parent::__construct(static::$defaultName);
        $this->scraper = $scraper;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scraper = $this->scraper;
        $this->progressBar = $this->createProgressBar($output);

        $scraper->start();
        $this->progressBar->finish();

        return Command::SUCCESS;
    }

    public function onLogEvent(ProcessEvent $event)
    {
        if ($this->progressBarStarted) {
            $this->showMessage($event);
        } else {
            $this->logger->info($event->getMessage(), $event->getContext());
        }
    }

    public function onLoopEvent(ProcessEvent $event)
    {
        $progressBar = $this->progressBar;
        $this->setMaxSteps($event);

        if (!$this->progressBarStarted) {
            $progressBar->start();
            $this->progressBarStarted = true;
        } else {
            $progressBar->advance();
        }
        $this->showMessage($event);
    }

    private function setMaxSteps(ProcessEvent $event)
    {
        if ($event->getCount() > 0 && 0 == $this->progressBar->getMaxSteps()) {
            $this->progressBar->setMaxSteps($event->getCount());
        }
    }

    private function createProgressBar(OutputInterface $output)
    {
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('%current%/%max% [%bar%] - %message%');
        $progressBar->setMessage('Processing Scrap Config ...');

        return $progressBar;
    }

    private function showMessage(ProcessEvent $event)
    {
        $context = $event->getContext();
        $message = $event->getMessage();
        $progressBar = $this->progressBar;
        $rendered = "<info>{$message}</info>";

        $replacements = [];
        foreach ($context as $key => $value) {
            $replacements['{'.$key.'}'] = "<comment>$value</comment>";
        }

        $rendered = strtr($rendered, $replacements);
        $progressBar->setMessage($rendered);
    }
}
