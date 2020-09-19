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

use RouterOS\Generator\Concerns\InteractsWithCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AnsibleTestCommandTest extends KernelTestCase
{
    use InteractsWithCommand;

    /**
     * @param array  $input
     * @param string $pattern
     * @param string $message
     * @dataProvider getTestExecuteData
     */
    public function testExecute(array $input, $pattern, $message = '')
    {
        $tester = $this->getCommandTester('ansible-test');
        $tester->execute($input);

        $display = $tester->getDisplay(true);
        $this->assertMatchesRegularExpression('#'.$pattern.'#', $display, $message);
    }

    public function getTestExecuteData()
    {
        return [
            [
                [],
                'Running Ansible Tests',
            ],
            [
                ['--type' => 'unit'],
                'Running Modules Unit Test',
            ],
            [
                ['--type' => 'facts'],
                'Running Facts Tests',
            ],
        ];
    }
}
