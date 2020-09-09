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

use Psr\Log\LoggerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Scraper\DocumentationScraper;
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
        $scraper->start();
        $output->writeln('');
        $output->writeln('Finished');

        return Command::SUCCESS;
    }
}
