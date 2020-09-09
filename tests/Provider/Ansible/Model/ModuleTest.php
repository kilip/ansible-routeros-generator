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

namespace Tests\RouterOS\Generator\Provider\Ansible\Model;

use Doctrine\ORM\EntityManagerInterface;
use RouterOS\Generator\Model\SubMenu;
use RouterOS\Generator\Provider\Ansible\Model\Module;
use RouterOS\Generator\Provider\Ansible\Model\ModuleManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class ModuleTest.
 */
class ModuleTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ModuleManager
     */
    private $manager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $this->em = $container->get('doctrine')
            ->getManager();

        $this->manager = new ModuleManager($this->em);
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @dataProvider getMutabilityData
     */
    public function testMutability($name, $value)
    {
        $manager = $this->manager;
        $model = $manager->create();

        $setter = "set{$name}";
        $getter = "get{$name}";

        // setter test
        $this->assertSame($model, \call_user_func([$model, $setter], $value));

        // getter test
        $this->assertSame($value, \call_user_func([$model, $getter]));
    }

    public function getMutabilityData()
    {
        return [
            ['name', 'test'],
            ['subMenu', new SubMenu()],
            ['configFile', __FILE__],
            ['config', ['some' => 'config']],
        ];
    }

    public function testCreateUpdate()
    {
        $manager = $this->manager;
        $model = $manager->findOrCreate('foo');
        $model->setConfigFile(__DIR__.'/../Fixtures/modules/interface.yml');
        $manager->update($model);

        $this->assertNotNull($model->getId());
    }

    public function testThrowOnInvalidConfigPath()
    {
        $model = new Module();

        $this->expectException(\InvalidArgumentException::class);
        $model->setConfigFile('foo');
    }
}
