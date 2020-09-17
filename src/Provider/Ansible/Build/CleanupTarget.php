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

namespace RouterOS\Generator\Provider\Ansible\Build;

use RouterOS\Generator\Event\BuildEvent;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CleanupTarget implements EventSubscriberInterface
{
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

    public function __construct(
        ModuleManagerInterface $moduleManager,
        string $modulePrefix,
        string $targetDir
    ) {
        $this->moduleManager = $moduleManager;
        $this->modulePrefix = $modulePrefix;
        $this->targetDir = $targetDir;
    }

    public static function getSubscribedEvents()
    {
        return [
            BuildEvent::PREPARE => 'onPrepare',
        ];
    }

    public function onPrepare(BuildEvent $event)
    {
        $moduleManager = $this->moduleManager;
        $modulePrefix = $this->modulePrefix;
        $targetDir = $this->targetDir;
        $modules = [];

        $event->log('Cleanup Target Dir');

        foreach ($moduleManager->getList() as $name => $config) {
            $module = $modulePrefix.$name;
            $package = $config['package'];

            $exp = explode('.', $package);
            $target = $targetDir.'/plugins/module_utils/resources/'.$exp[0];
            filesystem()->cleanupDirectory($target);

            $target = $targetDir."/plugins/modules/{$module}.py";
            filesystem()->cleanupDirectory($target);

            $target = $targetDir."/tests/integration/targets/{$module}";
            filesystem()->cleanupDirectory($target);
        }

        filesystem()->cleanupDirectory($targetDir.'/tests/unit/modules/fixtures/facts');
        filesystem()->cleanupDirectory($targetDir.'/tests/unit/modules/fixtures/units');
    }
}
