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
use RouterOS\Generator\Contracts\CompilerInterface;
use RouterOS\Generator\Contracts\MetaManagerInterface;
use RouterOS\Generator\Contracts\ResourceManagerInterface;
use RouterOS\Generator\Contracts\ScraperInterface;
use RouterOS\Generator\Processor\ScrapingProcessor;
use RouterOS\Generator\Structure\Meta;
use RouterOS\Generator\Structure\ResourceStructure;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tests\RouterOS\Generator\Concerns\InteractsWithContainer;
use Tests\RouterOS\Generator\Concerns\InteractsWithYaml;

class ScrappingProcessorTest extends KernelTestCase
{
    use InteractsWithContainer;
    use InteractsWithYaml;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var MockObject|MetaManagerInterface
     */
    private $metaManager;
    /**
     * @var MockObject|ResourceManagerInterface
     */
    private $resourceManager;
    /**
     * @var MockObject|CompilerInterface
     */
    private $templateCompiler;

    /**
     * @var MockObject|ScraperInterface
     */
    private $scraper;

    /**
     * @var string
     */
    private $resourceCompiledDir;

    /**
     * @var string
     */
    private $resourceConfigDir;

    /**
     * @var ScrapingProcessor
     */
    private $processor;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->metaManager = $this->createMock(MetaManagerInterface::class);
        $this->resourceManager = $this->createMock(ResourceManagerInterface::class);
        $this->templateCompiler = $this->createMock(CompilerInterface::class);
        $this->scraper = $this->createMock(ScraperInterface::class);
        $this->resourceCompiledDir = $container->getParameter('routeros.resource.compiled_dir');

        $this->processor = new ScrapingProcessor(
            $this->eventDispatcher,
            $this->metaManager,
            $this->resourceManager,
            $this->templateCompiler,
            $this->scraper,
            $this->resourceCompiledDir
        );
    }

    public function testProcess()
    {
        $metaManager = $this->metaManager;
        $processor = $this->processor;
        $metaCompiledFileName = __DIR__.'/../Fixtures/yaml/meta-compiled-interface.yaml';
        $scraper = $this->scraper;
        $compiler = $this->templateCompiler;
        $resourceCompiledDir = $this->resourceCompiledDir;

        $metaCompiledIndex = [
            'interface' => [
                'name' => 'interface',
                'config_file' => $metaCompiledFileName,
            ],
        ];

        $meta = new Meta();
        $meta->fromArray(Yaml::parseFile($metaCompiledFileName));
        $resource = new ResourceStructure();
        $resource->fromMeta($meta);

        $metaManager
            ->expects($this->once())
            ->method('getList')
            ->willReturn($metaCompiledIndex);

        $metaManager
            ->expects($this->once())
            ->method('getMeta')
            ->with('interface')
            ->willReturn($meta);

        $scraper->expects($this->once())
            ->method('scrapPage')
            ->with($meta)
            ->willReturn($resource);

        $compiler
            ->expects($this->at(0))
            ->method('compileYaml')
            ->with($this->isType('array'), "{$resourceCompiledDir}/interface/interface.yaml");

        $compiler
            ->expects($this->at(1))
            ->method('compileYaml')
            ->with($this->isType('array'), "{$resourceCompiledDir}/index.yaml");

        $processor->process();
    }
}
