<?php

namespace Tests\RouterOS\Generator\Listener;

use Psr\Log\LoggerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Listener\ConsoleProcessEventSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\TesterTrait;

class ConsoleProcessEventListenerTest extends TestCase
{
    use TesterTrait;

    public function testOnEventLoop()
    {
        $this->initOutput([]);

        $logger = $this->createMock(LoggerInterface::class);
        $output = $this->getOutput();
        $listener = new ConsoleProcessEventSubscriber($logger);
        $listener->setOutput($output);

        $listener->getProgressBar()->setRedrawFrequency(1);
        $listener->getProgressBar()->minSecondsBetweenRedraws(0);
        $listener->getProgressBar()->maxSecondsBetweenRedraws(0);


        $event = new ProcessEvent("Start Processing bar", [], 10);
        $listener->onStartProcessEvent($event);
        $this->assertRegExp("#Start Processing#", $this->getDisplay());
        for($i=1;$i<=10;$i++){
            $event
                ->setContext([$i])
                ->setMessage('Process {0}')
            ;
            $listener->onLoopEvent($event);

            $display = $this->getDisplay(true);
            $this->assertRegExp("#Process {$i}#", $display);
            $this->assertRegExp("#{$i}/10#", $display);
        }

        $listener->onEndProcessEvent();
    }
}
