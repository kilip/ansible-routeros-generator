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

namespace Tests\RouterOS\Generator\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use RouterOS\Generator\DependencyInjection\RouterosExtension;
use Symfony\Component\Yaml\Yaml;

class RouterosExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new RouterosExtension(),
        ];
    }

    public function testLoad()
    {
        $this->container->setParameter('kernel.environment', 'test');
        $this->load();
        $this->assertContainerBuilderHasParameter('ansible.compiled_dir');
    }

    public function getMinimalConfiguration(): array
    {
        $data = <<<EOC
config_dir: value
compiled_dir: value
cache_dir: value
ansible:
  git_repository: value
  module_prefix: value
  module_full_prefix: value
  target_dir: value
EOC;

        return Yaml::parse($data);
    }
}
