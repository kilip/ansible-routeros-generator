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
use Webmozart\Assert\Assert as MozartAssert;

class CompileCommandTest extends KernelTestCase
{
    use InteractsWithCommand;

    private static $isCompiled = false;

    /**
     * @param string $module
     * @param string $pattern
     * @param string $message
     * @param mixed  $file
     * @dataProvider getCompiledModulesData
     */
    public function testCompiledModules($file, $pattern, $message = null)
    {
        $this->compileTemplate();
        $targetDir = $this->getContainer()->getParameter('ansible.target_dir');
        $realpath = "{$targetDir}/$file";
        $contents = file_get_contents($realpath);

        if (null === $message) {
            $message = "file {$file} doesn't contain \"{$pattern}\"";
        }

        try {
            MozartAssert::contains($contents, $pattern, $message);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function getCompiledModulesData()
    {
        $author = $this->getParameter('ansible.default_author');

        return [
            ['plugins/modules/ros_bridge.py', 'AUTO GENERATED CODE', 'should have header'],
            ['plugins/modules/ros_bridge.py', 'DOCUMENTATION = """', 'should render documentation'],
            ['plugins/modules/ros_bridge.py', 'author: '.$author, 'should render module author'],
            ['plugins/modules/ros_bridge.py', 'EXAMPLES = """', 'should compile examples'],
            ['plugins/module_utils/resources/subset.py', 'from .interface.bridge.bridge import BridgeResource'],
            ['plugins/module_utils/resources/subset.py', 'bridge=BridgeResource'],
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
