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

namespace RouterOS\Generator\Provider\Ansible\QA;

use RouterOS\Generator\Event\BuildEvent;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Exception\ProcessException;
use RouterOS\Generator\Util\ProcessHelper;
use RouterOS\Generator\Util\ProcessItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Prepare implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $targetDir;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var ProcessItem[]
     */
    private $processItems;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        string $targetDir,
        ProcessHelper $processHelper = null
    ) {
        if (null === $processHelper) {
            $processHelper = new ProcessHelper();
        }
        $this->targetDir = $targetDir;
        $this->processHelper = $processHelper;
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            BuildEvent::TEST_PREPARE => 'onPrepare',
        ];
    }

    public function onPrepare(BuildEvent $event)
    {
        $dispatcher = $this->dispatcher;
        $exceptions = [];

        $event->log('Ansible Tests Preparation');

        $this->checkVirtualEnv();
        $this->checkRequirements();
        $this->runTox();

        $processEvent = new ProcessEvent('Start Test Preparations', [], \count($this->processItems));
        $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_START);

        foreach ($this->processItems as $item) {
            try {
                $processEvent->setMessage($item->getMessage());
                $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_LOOP);
                $this->runProcess($event, $item);
            } catch (ProcessException $e) {
                $exceptions[] = $e;
            }
        }

        $processEvent->setMessage('Completed');
        $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_END);

        foreach ($exceptions as $exception) {
            $event->logError($exception->getMessage(), $exception->getContext());
        }
    }

    public function checkVirtualEnv()
    {
        $processHelper = $this->processHelper;
        $targetDir = $this->targetDir;
        $venvDir = '.venv';

        if (!is_dir("{$targetDir}/{$venvDir}")) {
            $sysPython = $processHelper->findExecutable('python3');
            $this->addProcessItem(
                [
                    $sysPython,
                    '-m',
                    'venv',
                    $venvDir,
                ],
                'Creating Python Virtual Env'
            );
        }
    }

    public function checkRequirements()
    {
        $targetDir = $this->targetDir;
        $wheelLockFile = "{$targetDir}/.venv/.wheel.lck";
        $requirementsLock = "{$targetDir}/.venv/.requirements.lck";
        $testRequirementsLock = "{$targetDir}/.venv/.test-requirements.lck";

        if (!is_file($wheelLockFile)) {
            $this->addProcessItem(
                [
                    '.venv/bin/pip',
                    'install',
                    'wheel',
                ],
                'Installing wheel',
                function ($exitCode) use ($wheelLockFile) {
                    if (0 === $exitCode) {
                        touch($wheelLockFile);
                    }
                }
            );
        }

        if (!is_file($requirementsLock)) {
            $this->addProcessItem(
                [
                    '.venv/bin/pip',
                    'install',
                    '-r',
                    'requirements.txt',
                ],
                'Installing requirements',
                function ($exitCode) use ($requirementsLock) {
                    if (0 === $exitCode) {
                        touch($requirementsLock);
                    }
                }
            );
        }

        if (!is_file($testRequirementsLock)) {
            $this->addProcessItem(
                [
                    '.venv/bin/pip',
                    'install',
                    '-r',
                    'test-requirements.txt',
                ],
                'Installing test requirements',
                function ($exitCode) use ($testRequirementsLock) {
                    if (0 === $exitCode) {
                        touch($testRequirementsLock);
                    }
                }
            );
        }
    }

    public function runTox()
    {
        $this->addProcessItem(['.venv/bin/tox'], 'Run tox');
    }

    private function addProcessItem($cmds, $message, callable $afterProcess = null)
    {
        $this->processItems[] = new ProcessItem($cmds, $this->targetDir, $message, $afterProcess);
    }

    private function runProcess(BuildEvent $event, ProcessItem $item)
    {
        $processHelper = $this->processHelper;
        $processHelper->setDirectOutput(true);
        $processHelper->create($item->getCommands(), $item->getWorkingDir());
        $exitCode = $processHelper->run();
        if (\is_callable($item->getAfterProcess())) {
            \call_user_func($item->getAfterProcess(), $exitCode);
        }

        if (0 !== $exitCode) {
            $process = $processHelper->getProcess();
            $error = $process->getCommandLine();
            $exception = new ProcessException('Command {0} failed.');
            $exception->setContext([$error]);
            throw $exception;
        }
    }
}
