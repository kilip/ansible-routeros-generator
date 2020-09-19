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

namespace RouterOS\Generator\Event;

use RouterOS\Generator\Util\ProcessHelper;
use RouterOS\Generator\Util\Text;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

trait ProcessTrait
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var ProcessHelper
     */
    protected $processHelper;

    /**
     * @param string $message
     * @param array  $context
     * @param int    $count
     *
     * @return \RouterOS\Generator\Event\ProcessEvent
     */
    public function dispatchStartEvent($message = 'Starting', $context = [], $count = 0)
    {
        $dispatcher = $this->dispatcher;
        $event = new ProcessEvent($message, $context, $count);

        $dispatcher->dispatch($event, ProcessEvent::EVENT_START);

        return $event;
    }

    public function dispatchEndEvent($message = 'Completed', array $context = [])
    {
        $dispatcher = $this->dispatcher;
        $event = new ProcessEvent($message, $context);

        $dispatcher->dispatch($event, ProcessEvent::EVENT_END);

        return $event;
    }

    public function dispatchLoopEvent($message, $context = [])
    {
        $dispatcher = $this->dispatcher;
        $event = new ProcessEvent($message, $context);

        $dispatcher->dispatch($event, ProcessEvent::EVENT_LOOP);

        return $event;
    }

    public function dispatchExceptionEvent($message, $context = [], array $exceptions = [])
    {
        $dispatcher = $this->dispatcher;
        $event = new ProcessEvent($message, $context);
        $event->setExceptions($exceptions);

        $dispatcher->dispatch($event, ProcessEvent::EVENT_EXCEPTION);
    }

    public function runProcess($cmds, $workingDir)
    {
        $processHelper = $this->processHelper;
        $processHelper->setDirectOutput(false);

        $processHelper->create($cmds, $workingDir);
        $exitCode = $processHelper->run();
        if (0 !== $exitCode) {
            $process = $processHelper->getProcess();
            $format = <<<EOC
<comment>command:</comment> {0}
<comment>output:</comment> {1}
EOC;
            $messages = Text::decorateMessage($format, [
                $process->getCommandLine(),
                $process->getIncrementalErrorOutput(),
            ], false);
            throw new \Exception($messages);
        }
    }
}
