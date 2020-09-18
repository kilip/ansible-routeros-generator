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
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use RouterOS\Generator\Util\ProcessHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Facts implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var ModuleManagerInterface
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $targetDir;

    /**
     * @var string
     */
    private $modulePrefix;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ModuleManagerInterface $manager,
        string $targetDir,
        ProcessHelper $processHelper = null
    ) {
        if (null === $processHelper) {
            $processHelper = new ProcessHelper();
        }

        $this->dispatcher = $dispatcher;
        $this->moduleManager = $manager;
        $this->targetDir = $targetDir;
        $this->processHelper = $processHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            BuildEvent::TEST => 'onTest',
        ];
    }

    public function onTest(BuildEvent $event)
    {
        $processHelper = $this->processHelper;
        $targetDir = $this->targetDir;
        $dispatcher = $this->dispatcher;

        $cmds = [
            '.venv/bin/ansible-test',
            'units',
            '--python',
            '3.8',
            'tests/unit/modules/test_ros_facts.py',
        ];
        $processHelper->create($cmds, $targetDir)->run();
    }
}
