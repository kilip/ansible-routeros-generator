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

namespace Tests\RouterOS\Generator\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RouterOS\Generator\Model\SubMenu;
use RouterOS\Generator\Model\SubMenuManager;

class SubMenuManagerTest extends TestCase
{
    /**
     * @var MockObject|EntityManagerInterface
     */
    private $om;

    /**
     * @var MockObject|ObjectRepository
     */
    private $repository;

    /**
     * @var SubMenuManager
     */
    private $manager;

    protected function setUp(): void
    {
        $this->om = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(ObjectRepository::class);

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with(SubMenu::class)
            ->willReturn($this->repository);

        $this->manager = new SubMenuManager($this->om);
    }

    public function testFindOrCreate()
    {
        $repository = $this->repository;
        $manager = $this->manager;

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'test'])
            ->willReturn(null);

        $manager->findOrCreate('test');
    }

    public function testGetSubMenuList()
    {
        $om = $this->om;
        $manager = $this->manager;
        $builder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(QueryMock::class);

        $om->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('select')
            ->with(['a.id', 'a.name', 'a.package'])
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('from')
            ->with(SubMenu::class, 'a')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('setMaxResults')
            ->with(1000);

        $builder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn(['result']);

        $this->assertEquals(['result'], $manager->getSubMenuList());
    }
}
