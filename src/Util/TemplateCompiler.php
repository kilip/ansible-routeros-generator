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

namespace RouterOS\Generator\Util;

use RouterOS\Generator\Contracts\TemplateCompilerInterface;
use Twig\Environment as Twig;

class TemplateCompiler implements TemplateCompilerInterface
{
    /**
     * @var Twig
     */
    private $twig;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function compile(string $template, string $target, array $context): void
    {
        $twig = $this->twig;

        if (!is_dir($dir = \dirname($target))) {
            mkdir($dir, 0775, true);
        }

        $output = $twig->render($template, $context);
        file_put_contents($target, $output, LOCK_EX);
    }
}
