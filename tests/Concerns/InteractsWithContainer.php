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

namespace Tests\RouterOS\Generator\Concerns;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait InteractsWithContainer
{
    /**
     * @param string $name
     * @return mixed
     */
    protected function getParameter(string $name)
    {
        return $this->getContainer()->getParameter($name);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    protected function getKernel()
    {
        if (null === static::$kernel) {
            self::$kernel = self::createKernel();
            self::$kernel->boot();
        }

        return static::$kernel;
    }
}
