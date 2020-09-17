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

use Symfony\Component\Yaml\Yaml;

trait InteractsWithYaml
{
    public function parseYamlFile($file)
    {
        $file = __DIR__.'/../../tests/Fixtures/'.$file;

        return Yaml::parseFile($file);
    }
}
