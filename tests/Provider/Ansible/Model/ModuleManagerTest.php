<?php

namespace Tests\RouterOS\Generator\Provider\Ansible\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Provider\Ansible\Model\Module;
use RouterOS\Generator\Provider\Ansible\Model\ModuleManager;
use Tests\RouterOS\Generator\Model\QueryMock;

class ModuleManagerTest extends TestCase
{
    /**
     * @var MockObject|EntityManagerInterface
     */
    private $em;

    /**
     * @var ModuleManager
     */
    private $manager;

    /**
     * @var MockObject|ObjectRepository
     */
    private $repository;

    public function setUp()
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(ObjectRepository::class);

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->with(Module::class)
            ->willReturn($this->repository)
        ;

        $this->manager = new ModuleManager(
            $this->em
        );
    }

    public function testGetModuleList()
    {
        $em = $this->em;
        $manager = $this->manager;
        $builder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(QueryMock::class);

        $em->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('select')
            ->with('a.id, a.name')
            ->willReturn($builder);
        $builder->expects($this->once())
            ->method('from')
            ->with(Module::class, 'a')
            ->willReturn($builder);
        $builder->expects($this->once())
            ->method('setMaxResults')
            ->with(1000);

        $builder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($result = ['result']);

        $this->assertSame($result, $manager->getModuleList());
    }

    public function testFindOrCreateName()
    {
        $repository = $this->repository;
        $manager = $this->manager;

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'test'])
            ->willReturn(null)
        ;

        $object = $manager->findOrCreate('test');
        $this->assertEquals('test', $object->getName());
    }

    public function testUpdate()
    {
        $em = $this->em;
        $manager = $this->manager;
        $module = $this->createMock(Module::class);

        $em
            ->expects($this->once())
            ->method('persist')
            ->with($module);
        $em
            ->expects($this->once())
            ->method('flush');

        $manager->update($module);

    }
}
