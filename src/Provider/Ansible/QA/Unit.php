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

use RouterOS\Generator\Event\ProcessTrait;
use RouterOS\Generator\Exception\ProcessException;
use RouterOS\Generator\Provider\Ansible\Constant;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Provider\Ansible\Event\AnsibleTestEvent;
use RouterOS\Generator\Util\ProcessHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Unit implements EventSubscriberInterface
{
    use ProcessTrait;

    /**
     * @var ModuleManagerInterface
     */
    private $moduleManager;

    /**
     * @var Constant
     */
    private $constant;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ModuleManagerInterface $moduleManager,
        Constant $constant,
        ProcessHelper $processHelper = null
    ) {
        if (null === $processHelper) {
            $processHelper = new ProcessHelper();
        }

        $this->dispatcher = $dispatcher;
        $this->moduleManager = $moduleManager;
        $this->constant = $constant;
        $this->processHelper = $processHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            AnsibleTestEvent::UNIT => 'onRun',
            AnsibleTestEvent::ALL => 'onRun',
        ];
    }

    public function onRun(AnsibleTestEvent $event)
    {
        $module = $event->getModule();
        if (null !== $module) {
            $exception = null;
            try {
                $this->doRunModule($module);
            } catch (\Exception $exception) {
                $exception = $this->createProcessException($module, $exception);
            }
            if (null !== $exception) {
                $this->dispatchExceptionEvent('Module Unit Tests Failed', [], [$exception]);
            }
        } else {
            $this->doRun();
        }
    }

    private function doRun()
    {
        $moduleManager = $this->moduleManager;
        $list = $moduleManager->getList();
        $exceptions = [];

        $this->dispatchStartEvent('Starting...', [], \count($list));

        foreach ($list as $module => $config) {
            $this->dispatchLoopEvent('Unit Testing Module {0}', [$module]);
            try {
                $this->doRunModule($module);
            } catch (\Exception $e) {
                $exceptions[] = $this->createProcessException($module, $e);
            }
        }
        $this->dispatchEndEvent('Completed');

        if (\count($exceptions) > 0) {
            $this->dispatchExceptionEvent('Ansible Unit Test Module Failed', [], $exceptions);
        }
    }

    private function createProcessException($module, \Exception $e)
    {
        $processException = new ProcessException(
            "Test Unit <comment>{0}</comment> failed. message: \n{1}", 0, $e);
        $processException->setContext([$module, $e->getMessage()]);

        return $processException;
    }

    /**
     * @param string $module
     *
     * @throws \Exception
     */
    private function doRunModule(string $module)
    {
        $constant = $this->constant;
        $targetDir = $constant->getTargetDir();
        $testDir = str_replace($targetDir.'/', '', $constant->getModuleTestDir());
        $testFile = "{$testDir}/test_{$constant->getModulePrefix()}{$module}.py";
        $commands = [
            '.venv/bin/ansible-test',
            'units',
            '--python',
            '3.8',
            $testFile,
        ];
        $this->runProcess($commands, $targetDir);
    }
}
