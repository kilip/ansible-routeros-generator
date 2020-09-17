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

namespace RouterOS\Generator\Provider\Ansible\Config;

use RouterOS\Generator\Provider\Ansible\Event\ModuleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IntegrationConfig implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ModuleEvent::PRE_COMPILE => 'onPreCompile',
        ];
    }

    public function onPreCompile(ModuleEvent $event)
    {
        $module = $event->getModule();

        $integration = $module->getIntegration();

        $event->addConfig([
            'integration' => $integration,
        ]);
    }
}
