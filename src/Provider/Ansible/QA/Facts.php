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

use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Event\ProcessTrait;
use RouterOS\Generator\Exception\ProcessException;
use RouterOS\Generator\Provider\Ansible\Constant;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Provider\Ansible\Event\AnsibleTestEvent;
use RouterOS\Generator\Util\ProcessHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Facts implements EventSubscriberInterface
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
        ModuleManagerInterface $manager,
        Constant $constant,
        ProcessHelper $processHelper = null
    ) {
        if (null === $processHelper) {
            $processHelper = new ProcessHelper();
        }

        $this->dispatcher = $dispatcher;
        $this->moduleManager = $manager;

        $this->processHelper = $processHelper;
        $this->constant = $constant;
    }

    public static function getSubscribedEvents()
    {
        return [
            AnsibleTestEvent::ALL => 'onAnsibleTest',
            AnsibleTestEvent::FACTS => 'onAnsibleTest',
        ];
    }

    public function onAnsibleTest(AnsibleTestEvent $event)
    {
        $this->test();
    }

    public function test()
    {
        $constant = $this->constant;
        $dispatcher = $this->dispatcher;
        $moduleManager = $this->moduleManager;
        $targetDir = $constant->getTargetDir();
        $moduleList = $moduleManager->getList();
        $exceptions = [];

        $testFactsDir = str_replace(
            $targetDir.'/',
            '',
            $constant->getModuleTestFactsDir());

        $processEvent = new ProcessEvent('Starting ...', [], \count($moduleList));
        $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_START);

        foreach ($moduleList as $name => $config) {
            $processEvent->setMessage('Testing {0}', [$name]);
            $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_LOOP);

            $testFile = "{$testFactsDir}/test_{$name}.py";
            $cmds = [
                '.venv/bin/ansible-test',
                'units',
                '--python',
                '3.8',
                $testFile,
            ];
            try {
                $this->runProcess($cmds, $targetDir);
            } catch (\Exception $exception) {
                $processException = new ProcessException("Test facts {0} failed. message: \n{1}", 0, $exception);
                $processException->setContext([$name, $exception->getMessage()]);
                $exceptions[] = $processException;
            }
        }

        $processEvent->setMessage('Completed');
        $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_END);

        if (\count($exceptions) > 0) {
            $processEvent->setExceptions($exceptions);
            $dispatcher->dispatch($processEvent, ProcessEvent::EVENT_EXCEPTION);
        }
    }
}
