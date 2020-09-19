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

use RouterOS\Generator\Structure\ResourceStructure;
use RouterOS\Generator\Util\Text;
use Symfony\Component\Yaml\Yaml;

trait InteractsWithStructure
{
    use InteractsWithContainer;
    use InteractsWithYaml;

    protected function createResource($name)
    {
        $configDir = $this->getParameter('routeros.resource.compiled_dir');
        $config = $this->getConfig($configDir, $name);

        $resource = new ResourceStructure();
        $resource->fromArray($config);

        return $resource;
    }

    protected function getConfig($configDir, $name)
    {
        $nsPath = Text::namespaceToPath($name);
        $path = "{$configDir}/{$nsPath}.yaml";

        return Yaml::parseFile($path);
    }
}
