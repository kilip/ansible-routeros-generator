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

namespace Tests\RouterOS\Generator\Listener;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Listener\ProcessEventSubscriber;
use Symfony\Component\Console\Tester\TesterTrait;

class ProcessListenerTest extends TestCase
{
    use TesterTrait;

    public function testOnEventLoop()
    {
        $this->initOutput([]);

        $logger = $this->createMock(LoggerInterface::class);
        $output = $this->getOutput();
        $listener = new ProcessEventSubscriber($logger);
        $listener->setOutput($output);

        $event = new ProcessEvent('Start Processing bar', [], 10);
        $listener->onStartProcessEvent($event);

        $listener->getProgressBar()->setRedrawFrequency(1);
        $listener->getProgressBar()->minSecondsBetweenRedraws(0);
        $listener->getProgressBar()->maxSecondsBetweenRedraws(0);

        $this->assertMatchesRegularExpression('#Start Processing#', $this->getDisplay());

        for ($i = 1; $i <= 10; ++$i) {
            $event->setMessage('Process {0}', [$i]);
            $listener->onLoopEvent($event);

            $display = $this->getDisplay(true);
            $this->assertMatchesRegularExpression("#Process {$i}#", $display);
            $this->assertMatchesRegularExpression("#{$i}/10#", $display);
        }

        $listener->onEndProcessEvent($event);
    }
}
