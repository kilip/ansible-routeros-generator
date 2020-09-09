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

namespace Tests\RouterOS\Generator\Functional\Model;

use Doctrine\ORM\EntityManagerInterface;
use RouterOS\Generator\Model\SubMenuManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubMenuManagerFunctionalTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SubMenuManager
     */
    private $manager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()
            ->get('doctrine')->getManager();

        $this->manager = new SubMenuManager($this->em);
    }

    public function testGetSubMenuList()
    {
        $manager = $this->manager;
        $data = $manager->getSubMenuList();
        $this->assertIsArray($data);
    }
}
