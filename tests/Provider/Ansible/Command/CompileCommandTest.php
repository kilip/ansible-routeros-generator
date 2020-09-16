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

namespace Tests\RouterOS\Generator\Provider\Ansible\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\RouterOS\Generator\Concerns\InteractsWithCommand;

class CompileCommandTest extends KernelTestCase
{
    use InteractsWithCommand;

    private static $isCompiled = false;

    /**
     * @param string $module
     * @param string $pattern
     * @param string $message
     * @dataProvider getCompiledModulesData
     */
    public function testCompiledModules($module, $pattern, $message)
    {
        $this->compileTemplate();
        $targetDir = $this->getContainer()->getParameter('ansible.target_dir');
        $file = "{$targetDir}/plugins/modules/ros_{$module}.py";
        $contents = file_get_contents($file);
        $this->assertRegExp("#{$pattern}#", $contents, $message);
    }

    public function getCompiledModulesData()
    {
        return [
            ['bridge', 'AUTO GENERATED CODE', 'should have header'],
            ['bridge', 'EXAMPLES = """', 'should compile examples'],
        ];
    }

    private function compileTemplate()
    {
        if (!static::$isCompiled) {
            $tester = $this->getCommandTester('ansible:compile');
            $tester->execute([]);
            static::$isCompiled = true;
        }
    }
}
