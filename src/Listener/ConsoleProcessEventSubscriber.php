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

namespace RouterOS\Generator\Listener;

use Psr\Log\LoggerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConsoleProcessEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    private $progressBarStarted = false;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            ProcessEvent::EVENT_START => 'onStartProcessEvent',
            ProcessEvent::EVENT_LOOP => 'onLoopEvent',
            ProcessEvent::EVENT_END => 'onEndProcessEvent',
            ProcessEvent::EVENT_LOG => 'onLogEvent',
        ];
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
        $this->createProgressBar($output);
    }

    public function onLogEvent(ProcessEvent $event)
    {
        if ($this->progressBarStarted) {
            $this->showMessage($event);
        } else {
            $this->logger->info($event->getMessage(), $event->getContext());
        }
    }

    public function onStartProcessEvent(ProcessEvent $event)
    {
        if(!is_null($this->progressBar)){
            $this->showMessage($event);
            $this->progressBar->start();
            $this->setMaxSteps($event);
        }
    }

    public function onLoopEvent(ProcessEvent $event)
    {
        if(!is_null($this->progressBar)) {
            $progressBar = $this->progressBar;
            $this->showMessage($event);
            $progressBar->advance();
        }
    }

    public function onEndProcessEvent()
    {
        if(!is_null($this->progressBar)){
            $this->progressBar->finish();
        }
    }

    /**
     * @return ProgressBar
     */
    public function getProgressBar(): ProgressBar
    {
        return $this->progressBar;
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

        $this->progressBar = $progressBar;
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
