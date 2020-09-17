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

namespace RouterOS\Generator\Processor;

use RouterOS\Generator\Contracts\CompilerInterface;
use RouterOS\Generator\Contracts\MetaManagerInterface;
use RouterOS\Generator\Contracts\ResourceManagerInterface;
use RouterOS\Generator\Contracts\ScraperInterface;
use RouterOS\Generator\Event\BuildEvent;
use RouterOS\Generator\Event\ProcessEvent;
use RouterOS\Generator\Exception\ScrappingException;
use RouterOS\Generator\Structure\Meta;
use RouterOS\Generator\Structure\ResourceProperty;
use RouterOS\Generator\Structure\ResourceStructure;
use RouterOS\Generator\Util\Text;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ScrapingProcessor implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var MetaManagerInterface
     */
    private $metaManager;

    /**
     * @var ResourceManagerInterface
     */
    private $resourceManager;

    /**
     * @var CompilerInterface
     */
    private $templateCompiler;

    /**
     * @var string
     */
    private $compiledResourceDir;

    /**
     * @var ScraperInterface
     */
    private $scraper;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        MetaManagerInterface $metaManager,
        ResourceManagerInterface $resourceManager,
        CompilerInterface $templateCompiler,
        ScraperInterface $scraper,
        string $compiledResourceDir
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->metaManager = $metaManager;
        $this->resourceManager = $resourceManager;
        $this->templateCompiler = $templateCompiler;
        $this->compiledResourceDir = $compiledResourceDir;
        $this->scraper = $scraper;
    }

    public static function getSubscribedEvents()
    {
        return [
            BuildEvent::PREPARE => ['onBuildEvent', 999],
        ];
    }

    public function onBuildEvent(BuildEvent $event)
    {
        $event->getOutput()->writeln('<info>Generating Resources</info>');
        $this->process();
    }

    public function process()
    {
        $metaManager = $this->metaManager;
        $metas = $metaManager->getList();
        $dispatcher = $this->eventDispatcher;

        $resources = [];
        $index = [];
        $exceptions = [];

        $event = new ProcessEvent('Starting...', [], \count($metas));
        $dispatcher->dispatch($event, ProcessEvent::EVENT_START);
        foreach ($metas as $name => $config) {
            $event->setMessage('Scraping {0}')->setContext([$name]);
            $dispatcher->dispatch($event, ProcessEvent::EVENT_LOOP);
            $meta = $metaManager->getMeta($name);
            $resource = $this->processMeta($meta);
            $resources[] = $resource;
            $configFileName = $this->getCompiledResourceFilename($resource);
            $index[$name] = [
                'name' => $name,
                'config_file' => $configFileName,
            ];
            if ($resource->hasException()) {
                $exceptions[$name] = $resource;
            }
        }
        $event->setMessage('Completed');
        $dispatcher->dispatch($event, ProcessEvent::EVENT_END);

        $this->compileResources($resources);
        $this->compileResourceIndex($index);

        if (\count($exceptions) > 0) {
            $metaExceptions = [];
            foreach ($exceptions as $exception) {
                $metaExceptions[] = new ScrappingException($exception);
            }
            $event = new ProcessEvent('Scrapping Process Failed.', []);
            $event->setExceptions($metaExceptions);
            $dispatcher->dispatch($event, ProcessEvent::EVENT_EXCEPTION);
        }
    }

    public function processMeta(Meta $meta): ?ResourceStructure
    {
        $scraper = $this->scraper;
        $resource = $scraper->scrapPage($meta);

        if ('config' !== $resource->getType()) {
            if (
                !$resource->hasProperty('comment')
                && !$resource->hasOption(ResourceStructure::OPTIONS_NON_COMMENTED)
            ) {
                $property = new ResourceProperty();
                $property
                    ->setName('comment')
                    ->setOriginalName('comment')
                    ->setType('string')
                    ->setDescription('Short note for '.$resource->getName().' resource');
                $resource->addProperty($property);
            }

            if (
                !$resource->hasProperty('disabled')
                && !$resource->hasOption(ResourceStructure::OPTIONS_NON_DISABILITY)
            ) {
                $property = new ResourceProperty();
                $property
                    ->setName('disabled')
                    ->setOriginalName('disabled')
                    ->setType('string')
                    ->setChoices(['yes', 'no'])
                    ->setDefault('no')
                    ->setDescription('Set '.$resource->getName().' resource disability');
                $resource->addProperty($property);
            }
        }

        return $resource;
    }

    /**
     * @param array|ResourceStructure[] $resources
     */
    private function compileResources($resources)
    {
        $compiler = $this->templateCompiler;
        $priorities = [
            'properties',
            'options',
            'keys',
            'command',
            'type',
            'package',
            'name',
        ];

        foreach ($resources as $resource) {
            $config = $resource->toArray();
            $config = Text::arrayKeySort($priorities, $config);
            ksort($config['properties']);
            $target = $this->getCompiledResourceFilename($resource);
            $compiler->compileYaml($config, $target);
        }
    }

    private function compileResourceIndex(array $index)
    {
        $targetDir = $this->compiledResourceDir;
        $compiler = $this->templateCompiler;
        $target = "{$targetDir}/index.yaml";

        $compiler->compileYaml($index, $target);
    }

    private function getCompiledResourceFilename(ResourceStructure $resource): string
    {
        $targetDir = $this->compiledResourceDir;
        $name = $resource->getName();
        $package = str_replace('.', '/', $resource->getPackage());

        return "{$targetDir}/{$package}/{$name}.yaml";
    }
}
