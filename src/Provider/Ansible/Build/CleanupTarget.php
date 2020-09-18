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
use RouterOS\Generator\Provider\Ansible\Constant;
use RouterOS\Generator\Provider\Ansible\Contracts\ModuleManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CleanupTarget implements EventSubscriberInterface
{
    /**
     * @var ModuleManagerInterface
     */
    private $moduleManager;
    /**
     * @var Constant
     */
    private $constant;

    public function __construct(
        ModuleManagerInterface $moduleManager,
        Constant $constant
    ) {
        $this->moduleManager = $moduleManager;
        $this->constant = $constant;
    }

    public static function getSubscribedEvents()
    {
        return [
            BuildEvent::PREPARE => 'onPrepare',
        ];
    }

    public function onPrepare(BuildEvent $event)
    {
        $constant = $this->constant;
        $moduleManager = $this->moduleManager;
        $modulePrefix = $constant->getModulePrefix();

        $event->log('Cleanup Target Dir');

        foreach ($moduleManager->getList() as $name => $config) {
            $module = $modulePrefix.$name;
            $package = $config['package'];

            $exp = explode('.', $package);
            $target = "{$constant->getResourcesDir()}/".$exp[0];
            filesystem()->remove($target);

            $target = "{$constant->getModulesDir()}/{$module}.py";
            filesystem()->remove($target);

            $target = "{$constant->getModuleIntegrationDir()}/{$module}";
            filesystem()->remove($target);
        }

        filesystem()->remove($constant->getModuleTestFactsDir());
        filesystem()->remove($constant->getModuleTestDir());
    }
}
