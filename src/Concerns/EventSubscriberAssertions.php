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

namespace RouterOS\Generator\Concerns;

trait EventSubscriberAssertions
{
    protected $subscriberEventClass;

    protected function assertSubscribedEvent($name, $expectedMethod, $expectedPriority = null)
    {
        $class = $this->subscriberEventClass;
        $this->assertTrue(class_exists($this->subscriberEventClass));

        $callable = [$class, 'getSubscribedEvents'];
        $events = \call_user_func($callable);

        $this->assertArrayHasKey($name, $events, "Event {$name} is not subscribed");

        $descriptions = $events[$name];
        $priority = null;
        if (\is_array($descriptions)) {
            list($method, $priority) = $events[$name];
        } else {
            $method = $descriptions;
        }
        $this->assertSame($expectedMethod, $method);

        if (null !== $expectedPriority) {
            if (null === $priority) {
                $this->fail('Event priority is not set');
            }

            $this->assertSame($expectedPriority, $priority, 'Event priority is not same');
        }
    }
}
