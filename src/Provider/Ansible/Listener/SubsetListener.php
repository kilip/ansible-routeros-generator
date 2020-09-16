<?php


namespace RouterOS\Generator\Provider\Ansible\Listener;


use RouterOS\Generator\Provider\Ansible\Event\ModuleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubsetListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ModuleEvent::PRE_COMPILE => 'onPreCompile'
        ];
    }

    public function onPreCompile(ModuleEvent $event)
    {
        
    }
}