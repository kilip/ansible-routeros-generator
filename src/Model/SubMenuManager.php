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

namespace RouterOS\Generator\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use RouterOS\Generator\Contracts\SubMenuManagerInterface;

class SubMenuManager implements SubMenuManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var int
     */
    private $maxResults;

    public function __construct(
        EntityManagerInterface $manager,
        $maxResults = 1000
    ) {
        $this->manager = $manager;
        $this->maxResults = $maxResults;
    }

    public function create(): SubMenu
    {
        return new SubMenu();
    }

    public function findOrCreate(string $name)
    {
        $object = $this->findByName($name);

        if (!\is_object($object)) {
            $object = $this->create();
            $object->setName($name);
        }

        return $object;
    }

    public function findByName(string $name)
    {
        return $this->getRepository()->findOneBy(['name' => $name]);
    }

    public function update(SubMenu $object, bool $andFlush = true): void
    {
        $om = $this->manager;

        $om->persist($object);
        if ($andFlush) {
            $om->flush();
        }
    }

    public function getSubMenuList(): array
    {
        $om = $this->manager;
        $builder = $om->createQueryBuilder();
        $maxResults = $this->maxResults;

        $builder
            ->select(['a.id', 'a.name', 'a.package'])
            ->from(SubMenu::class, 'a');

        $builder->setMaxResults($maxResults);

        return $builder->getQuery()->getArrayResult();
    }

    public function getRepository(): ObjectRepository
    {
        return $this->manager->getRepository(SubMenu::class);
    }
}
