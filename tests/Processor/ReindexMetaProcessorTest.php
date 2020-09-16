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

namespace Tests\RouterOS\Generator\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use RouterOS\Generator\Contracts\CacheManagerInterface;
use RouterOS\Generator\Contracts\CompilerInterface;
use RouterOS\Generator\Contracts\MetaManagerInterface;
use RouterOS\Generator\Processor\ReindexMetaProcessor;
use RouterOS\Generator\Structure\MetaConfiguration;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tests\RouterOS\Generator\Concerns\InteractsWithContainer;

class ReindexMetaProcessorTest extends KernelTestCase
{
    use InteractsWithContainer;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var MockObject|CacheManagerInterface
     */
    private $cacheManager;

    /**
     * @var MockObject|MetaManagerInterface
     */
    private $metaManager;

    /**
     * @var string
     */
    private $metaConfigDir;

    /**
     * @var ReindexMetaProcessor
     */
    private $processor;

    /**
     * @var MockObject|CompilerInterface
     */
    private $templateCompiler;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->cacheManager = $this->createMock(CacheManagerInterface::class);
        $this->templateCompiler = $this->createMock(CompilerInterface::class);
        $this->metaConfigDir = __DIR__.'/../Fixtures/config/meta';

        $this->processor = new ReindexMetaProcessor(
            $this->dispatcher,
            $this->getContainer()->get('routeros.util.cache_manager'),
            new MetaConfiguration(),
            $this->templateCompiler,
            $this->getContainer()->getParameter('routeros.meta.config_dir'),
            $this->getContainer()->getParameter('routeros.meta.compiled_dir')
        );
    }

    public function testProcess()
    {
        $templateCompiler = $this->templateCompiler;
        $processor = $this->processor;

        $templateCompiler
            ->expects($this->atLeastOnce())
            ->method('compileYaml')
            ->with($this->isType('array'), $this->isType('string'));

        $processor->process();
    }
}
