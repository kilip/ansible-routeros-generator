<?php

namespace Tests\RouterOS\Generator\Provider\Ansible\Model;

use Doctrine\ORM\EntityManagerInterface;
use RouterOS\Generator\Model\SubMenu;
use RouterOS\Generator\Provider\Ansible\Model\ModuleManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use RouterOS\Generator\Provider\Ansible\Model\Module;

/**
 * Class ModuleTest
 *
 * @package Tests\RouterOS\Generator\Provider\Ansible\Model
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

    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $this->em = $container->get('doctrine')
            ->getManager();

        $this->manager = new ModuleManager($this->em);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @dataProvider getMutabilityData
     */
    public function testMutability($name, $value)
    {
        $manager = $this->manager;
        $model = $manager->create();

        $setter = "set{$name}";
        $getter = "get{$name}";


        // setter test
        $this->assertSame($model, call_user_func([$model, $setter], $value));

        // getter test
        $this->assertSame($value, call_user_func([$model, $getter]));
    }

    public function getMutabilityData()
    {
        return [
            ['name', 'test'],
            ['subMenu', new SubMenu()],
            ['configFile', "some_file"],
            ['config', ['some' => 'config']],
        ];
    }


    public function testCreateUpdate()
    {
        $manager = $this->manager;

        $model = new Module();
        $model->setName('foo');

        $manager->update($model);

        $this->assertNotNull($model->getId());
    }
}
