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

class ProcessEventSubscriber implements EventSubscriberInterface
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

    /**
     * @var \Exception[]
     */
    private $exceptions = [];

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            ProcessEvent::EVENT_START => 'onStartProcessEvent',
            ProcessEvent::EVENT_LOOP => 'onLoopEvent',
            ProcessEvent::EVENT_END => 'onEndProcessEvent',
            ProcessEvent::EVENT_LOG => 'onLogEvent',
            ProcessEvent::EVENT_EXCEPTION => 'onExceptionEvent',
        ];
    }

    public function hasException()
    {
        return \count($this->exceptions) > 0;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
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
        $this->createProgressBar($this->output);

        if (null !== $this->progressBar) {
            $this->showMessage($event);
            $this->progressBar->start();
            $this->setMaxSteps($event);
        }
        $this->progressBarStarted = true;
    }

    public function onLoopEvent(ProcessEvent $event)
    {
        if (null !== $this->progressBar) {
            $progressBar = $this->progressBar;
            $this->showMessage($event);
            $progressBar->advance();
        }
    }

    public function onEndProcessEvent(ProcessEvent $event)
    {
        $this->showMessage($event);
        if (null !== $this->progressBar) {
            $this->progressBar->finish();
        }
        $this->progressBarStarted = false;
        $this->output->writeln("\n");
    }

    public function onExceptionEvent(ProcessEvent $event)
    {
        $this->exceptions = $event->getExceptions();
        $this->renderException();
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

        $progressBar->setRedrawFrequency(1);
        $progressBar->minSecondsBetweenRedraws(0);
        $progressBar->maxSecondsBetweenRedraws(0);

        $this->progressBar = $progressBar;
    }

    private function showMessage(ProcessEvent $event)
    {
        $progressBar = $this->progressBar;
        $context = $event->getContext();
        $message = $event->getMessage();

        $rendered = $this->renderMessage($message, $context);
        $progressBar->setMessage($rendered);
    }

    public function renderException()
    {
        $output = $this->output;

        foreach ($this->exceptions as $exception) {
            $callback = [$exception, 'getContext'];
            $message = $exception->getMessage();

            if (\is_callable($callback)) {
                $context = \call_user_func($callback);
                $message = $this->renderMessage($message, $context, false);
            }
            $output->writeln("<error>error</error> {$message}");
            $output->writeln('');
        }
    }

    private function renderMessage($message, array $context = [], $decorate = true)
    {
        $rendered = "<info>{$message}</info>";
        $replacements = [];
        foreach ($context as $key => $value) {
            if ($decorate) {
                $value = "<comment>{$value}</comment>";
            }
            $replacements['{'.$key.'}'] = $value;
        }

        return strtr($rendered, $replacements);
    }
}
