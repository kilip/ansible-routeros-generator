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

interface TemplateCompilerInterface
{
    /**
     * @param string $template
     * @param string $target
     * @param array  $context
     */
    public function compile(string $template, string $target, array $context): void;
}
