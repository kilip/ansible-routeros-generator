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

namespace RouterOS\Generator\Contracts;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ProviderInterface
{
    /**
     * @return string
     */
    public function getConfigKey(): string;

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @return void
     */
    public function load(ContainerBuilder $container, array $config): void;

    /**
     * @param ArrayNodeDefinition $builder
     *
     * @return void
     */
    public function configure(ArrayNodeDefinition $builder): void;
}
