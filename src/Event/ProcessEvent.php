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

namespace RouterOS\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ProcessEvent extends Event
{
    public const EVENT_LOOP = 'routeros.process.loop';
    public const EVENT_LOG = 'routeros.process.log';

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $context;

    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $current;

    public function __construct(
        string $message,
        array $context,
        int $count = 0,
        int $current = 0
    ) {
        $this->message = $message;
        $this->context = $context;
        $this->count = $count;
        $this->current = $current;
    }

    public function getRenderedMessage(): string
    {
        $message = $this->message;

        foreach ($this->context as $key => $value) {
            $message = str_replace('{'.$key.'}', $value, $message);
        }

        return $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return static
     */
    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     *
     * @return static
     */
    public function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return static
     */
    public function setCount(int $count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->current;
    }

    /**
     * @param int $current
     *
     * @return static
     */
    public function setCurrent(int $current)
    {
        $this->current = $current;

        return $this;
    }
}
