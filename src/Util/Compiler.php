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

use RouterOS\Generator\Contracts\CompilerInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment as Twig;

class Compiler implements CompilerInterface
{
    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var string
     */
    private $kernelProjectDir;

    public function __construct(
        Twig $twig,
        string $kernelProjectDir
    ) {
        $this->twig = $twig;
        $this->kernelProjectDir = $kernelProjectDir;
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

    public function compileYaml(array $config, string $target): void
    {
        $output = Yaml::dump($config, 6, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        $output = Text::fixYamlDump($output);
        $output = str_replace($this->kernelProjectDir.\DIRECTORY_SEPARATOR, '', $output);
        if (!is_dir($dir = \dirname($target))) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($target, $output, LOCK_EX);
    }
}
