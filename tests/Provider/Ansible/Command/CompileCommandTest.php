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

    public function verifyContains($expected, $contents)
    {
        $this->assertContains($expected, $contents);
    }

    public function testCompileUnitTests()
    {
        $this->compileTemplate();
        $file = $this->getRealpath('tests/unit/modules/fixtures/units/interface.bridge.bridge.yaml');
        $this->assertFileExists($file);
        $contents = file_get_contents($file);
        $this->assertStringContainsString(
            'module: ros_bridge',
            $contents
        );
        $this->assertStringContainsString(
            '/interface bridge set [ find name=br-wan ] arp=enabled comment="updated comment"',
            $contents
        );
    }

    public function testCompileFacts()
    {
        $this->compileTemplate();
        $file = $this->getRealpath('tests/unit/modules/fixtures/facts/interface.bridge.bridge.yaml');
        $contents = file_get_contents($file);
        $expected = <<<EOC
asserts:
  - 'self.assertEqual(result[0]["arp"], "reply-only")'
  - 'self.assertEqual(result[0]["comment"], "trunk bridge")'
  - 'self.assertEqual(result[0]["name"], "br-trunk")'
  - 'self.assertEqual(result[1]["arp"], "reply-only")'
  - 'self.assertEqual(result[1]["comment"], "wan bridge")'
  - 'self.assertEqual(result[1]["name"], "br-wan")'
resource: bridge
fixtures: |
  # RouterOS Output
  #
  /interface bridge
  add arp=reply-only comment="trunk bridge" name=br-trunk
  add arp=reply-only comment="wan bridge" name=br-wan


EOC;
        $this->assertEquals($expected, $contents);
    }

    /**
     * @param mixed  $file
     * @param string $pattern
     * @param string $message
     * @dataProvider getCompiledModulesData
     */
    public function testCompiledModules($file, $pattern, $message = null)
    {
        $this->compileTemplate();
        $realpath = $this->getRealpath($file);
        $contents = file_get_contents($realpath);

        if (null === $message) {
            $message = "file {$file} doesn't contain \"{$pattern}\"";
        }

        $this->assertStringContainsString($pattern, $contents, $message);
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

    private function getRealpath($file)
    {
        $targetDir = $this->getContainer()->getParameter('ansible.target_dir');

        return "{$targetDir}/$file";
    }
}
